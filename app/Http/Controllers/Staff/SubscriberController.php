<?php

namespace App\Http\Controllers\Staff;

use App\Contracts\SubscriberProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriberContract;
use App\Models\AuditLog;
use App\Models\MockSubscriberSession;
use App\Models\Payment;
use App\Services\Audit\AuditLogService;
use App\Services\Subscriber\SubscriberNotificationService;
use App\Services\Staff\SubscriberOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly SubscriberOperationsService $operations,
        private readonly AuditLogService $auditLog,
    ) {}

    public function create(): View
    {
        return view('staff.subscribers.create-contract', [
            'packages' => $this->subscriberProvider->listAvailablePackages(),
            'isAdmin' => auth()->user()->hasRole('admin'),
        ]);
    }

    public function storeContract(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'package_id' => ['required', 'string'],
            'contract_number' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', 'unique:mock_subscribers,contract_number', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'details' => ['nullable', 'array'],
            'details.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $packageId = $validated['package_id'];
        abort_unless(isset($this->subscriberProvider->listAvailablePackages()[$packageId]), 422, 'الباقة المحددة غير متاحة.');

        $subscriber = $this->subscriberProvider->provisionSubscriber(
            $validated['name'], $validated['phone'], $packageId, $validated['contract_number'],
        );

        $portalUser = User::create([
            'name' => $validated['name'],
            'username' => $subscriber->contractNumber,
            'contract_number' => $subscriber->contractNumber,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'type' => 'subscriber',
            'status' => \App\Enums\UserStatus::Active,
            'is_active' => true,
        ]);
        $portalUser->assignRole('subscriber');

        $contractDetails = array_intersect_key($validated['details'] ?? [], array_flip([
            'automatic_renewal', 'borrowing_enabled',
            'first_name', 'father_name', 'last_name', 'nationality', 'city', 'address',
            'passport_number', 'payment_method', 'notes',
        ]));
        if ($request->user()->hasRole('admin')) {
            $contractDetails = array_merge($contractDetails, array_intersect_key($validated['details'] ?? [], array_flip([
                'ip_address', 'sender_device_ip', 'download_speed', 'upload_speed',
                'mac_address', 'primary_dns', 'secondary_dns', 'device_port',
                'start_date', 'end_date', 'download_start', 'download_end',
                'download_quota', 'upload_quota', 'throttle_speed',
            ])));
        }

        SubscriberContract::create([
            'contract_number' => $subscriber->contractNumber,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'national_id' => $validated['national_id'] ?? null,
            'email' => $validated['email'] ?? null,
            'package_id' => $packageId,
            'details' => $contractDetails,
            'created_by' => $request->user()->id,
        ]);

        $this->auditLog->log('SUBSCRIBER_CONTRACT_CREATED', 'SubscriberContract', null, null, [
            'contract_number' => $subscriber->contractNumber,
            'name' => $validated['name'],
        ]);

        return redirect()->route('staff.subscribers.index', ['contract' => $subscriber->contractNumber])
            ->with('status', 'تم إنشاء العقد بنجاح. رقم العقد: '.$subscriber->contractNumber);
    }

    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        $results = $this->subscriberProvider->searchSubscribers($query);
        $packages = $this->subscriberProvider->listAvailablePackages();

        $selected = null;
        $contractRecord = null;
        $weeklyUsage = [];
        $sessions = collect();
        $payments = collect();
        $activity = collect();
        $contract = trim((string) $request->input('contract', ''));

        if ($contract !== '') {
            try {
                $selected = $this->operations->profile($contract);
                $contractRecord = SubscriberContract::with('creator')->where('contract_number', $contract)->first();
                $weeklyUsage = $this->subscriberProvider->getUsageHistory($contract, 7);
                $activeSessionCount = MockSubscriberSession::where('contract_number', $contract)->whereNull('ended_at')->count();
                $sessions = MockSubscriberSession::where('contract_number', $contract)
                    ->latest('started_at')->limit(10)->get();
                $payments = Payment::with('processor')
                    ->where('contract_number', $contract)
                    ->latest('paid_at')->limit(20)->get();
                $activity = AuditLog::with('user')
                    ->where(function ($builder) use ($contract) {
                        $builder->where('new_values->contract_number', $contract)
                            ->orWhere('old_values->contract_number', $contract);
                    })
                    ->latest()->limit(30)->get();
            } catch (\Throwable $e) {
                $selected = null;
                session()->now('error', $this->operations->actionErrorMessage($e));
            }
        }

        $portalAccounts = User::subscribers()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('contract_number', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('staff.subscribers.index', [
            'query' => $query,
            'results' => $results,
            'selected' => $selected,
            'packages' => $packages,
            'portalAccounts' => $portalAccounts,
            'contractRecord' => $contractRecord,
            'weeklyUsage' => $weeklyUsage,
            'sessions' => $sessions,
            'activeSessionCount' => $activeSessionCount ?? 0,
            'payments' => $payments,
            'activity' => $activity,
        ]);
    }


    public function recharge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_number' => ['required', 'string'],
            'package_id' => ['required', 'string'],
        ]);

        try {
            $result = $this->operations->recharge($validated['contract_number'], $validated['package_id'], $request->user());

            return back()->with('status', $result->message ?? 'تم شحن الباقة بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->operations->actionErrorMessage($e));
        }
    }

    public function changePackage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_number' => ['required', 'string'],
            'package_id' => ['required', 'string'],
        ]);

        try {
            $result = $this->operations->changePackage($validated['contract_number'], $validated['package_id']);

            return back()->with('status', $result->message ?? 'تم تغيير الباقة بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->operations->actionErrorMessage($e));
        }
    }

    public function activateBorrowing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_number' => ['required', 'string'],
        ]);

        try {
            $this->operations->activateEmergencyBorrowing($validated['contract_number']);

            return back()->with('status', 'تم تفعيل سلفة الطوارئ بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->operations->actionErrorMessage($e));
        }
    }

    public function sendMessage(Request $request, string $contractNumber, SubscriberNotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $notification = $notifications->sendToContract(
            $contractNumber,
            $validated['title'],
            $validated['message'],
            'info',
            sender: $request->user(),
        );

        if (! $notification) {
            return back()->with('error', 'لا يوجد حساب بوابة مرتبط بهذا العقد لإرسال الرسالة.');
        }

        $this->auditLog->log('SUBSCRIBER_MESSAGE_SENT', 'OperationalNotification', $notification->id, null, [
            'contract_number' => $contractNumber,
            'title' => $notification->title,
        ], actor: $request->user());

        return back()->with('status', 'تم إرسال الرسالة إلى بوابة المشترك.');
    }
}
