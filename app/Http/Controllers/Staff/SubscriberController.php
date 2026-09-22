<?php

namespace App\Http\Controllers\Staff;

use App\Contracts\SubscriberProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditLogService;
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

    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));
        $results = $this->subscriberProvider->searchSubscribers($query);
        $packages = $this->subscriberProvider->listAvailablePackages();

        $selected = null;
        $contract = trim((string) $request->input('contract', ''));

        if ($contract !== '') {
            try {
                $selected = $this->operations->profile($contract);
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'اسم المشترك مطلوب.',
            'username.required' => 'اسم المستخدم مطلوب.',
            'username.unique' => 'اسم المستخدم مستخدم مسبقاً.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مستخدم مسبقاً.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'type' => 'subscriber',
            'status' => \App\Enums\UserStatus::Active,
            'is_active' => true,
        ]);

        $user->assignRole('subscriber');

        $this->auditLog->log('SUBSCRIBER_CREATED', 'User', $user->id, null, [
            'name' => $user->name,
            'username' => $user->username,
            'phone' => $user->phone,
        ]);

        return back()->with('status', 'تمت إضافة حساب المشترك في البوابة بنجاح.');
    }

    public function recharge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contract_number' => ['required', 'string'],
            'package_id' => ['required', 'string'],
        ]);

        try {
            $result = $this->operations->recharge($validated['contract_number'], $validated['package_id']);

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
}
