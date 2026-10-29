<?php

namespace App\Http\Controllers\Subscriber;

use App\Contracts\SubscriberProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\OperationalNotification;
use App\Services\Subscriber\SubscriberDashboardService;
use App\Services\Subscriber\SubscriberNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly SubscriberDashboardService $dashboardService,
        private readonly SubscriberNotificationService $notificationsService,
    ) {}

    private function contractNumber(Request $request): string
    {
        $user = $request->user();

        if (! empty($user->contract_number)) {
            return (string) $user->contract_number;
        }

        if (is_string($user->username) && str_starts_with($user->username, 'TEST-')) {
            return $user->username;
        }

        abort(403, 'No subscriber contract is linked to this account.');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $contractNumber = $this->contractNumber($request);

        $dashboard = $this->dashboardService->build($contractNumber);

        $packages = $this->subscriberProvider->listAvailablePackages();
        $complaints = Complaint::where('user_id', $user->id)
            ->orWhere('contract_number', $contractNumber)
            ->orderByDesc('created_at')
            ->get();

        $payments = Payment::where('user_id', $user->id)
            ->orWhere('contract_number', $contractNumber)
            ->orderByDesc('created_at')
            ->get();

        $notifications = OperationalNotification::where('user_id', $user->id)
            ->latest()->limit(30)->get();

        return view('subscriber.dashboard', compact(
            'user',
            'contractNumber',
            'dashboard',
            'packages',
            'complaints',
            'payments',
            'notifications'
        ));
    }

    public function data(Request $request): JsonResponse
    {
        $contractNumber = $this->contractNumber($request);
        $dashboard = $this->dashboardService->build($contractNumber);

        return response()->json($dashboard)->header('Cache-Control', 'no-store');
    }

    public function recharge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'string'],
        ]);

        $contractNumber = $this->contractNumber($request);

        try {
            $result = $this->subscriberProvider->recharge($contractNumber, $validated['package_id']);
            $package = $this->subscriberProvider->listAvailablePackages()[$validated['package_id']];
            $this->notificationsService->notifyRecharge(
                $contractNumber,
                $package['name'],
                'subscriber-recharge-'.($result->transactionId ?: (string) \Illuminate\Support\Str::uuid()),
                $request->user(),
            );

            return back()->with('status', $result->message ?? 'تم شحن الحساب بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function activateBorrowing(Request $request): RedirectResponse
    {
        $contractNumber = $this->contractNumber($request);

        try {
            $this->subscriberProvider->activateEmergencyBorrowing($contractNumber);

            return back()->with('status', 'تم تفعيل سلفة الطوارئ بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markNotificationRead(Request $request, OperationalNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        $notification->update(['read_at' => now()]);

        return back();
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
        ], [
            'subject.required' => 'موضوع الشكوى مطلوب.',
        ]);

        $user = $request->user();
        $contractNumber = $this->contractNumber($request);

        Complaint::create([
            'user_id' => $user->id,
            'contract_number' => $contractNumber,
            'subscriber_name' => $user->name,
            'subject' => $validated['subject'],
            'type' => $validated['type'] ?? 'استفسار عام',
            'priority' => 'medium',
            'status' => 'open',
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('status', 'تم تقديم الشكوى بنجاح وسيتم متابعتها من قبل فريق الدعم الفني.');
    }
}
