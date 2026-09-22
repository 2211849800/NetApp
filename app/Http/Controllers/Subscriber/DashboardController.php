<?php

namespace App\Http\Controllers\Subscriber;

use App\Contracts\SubscriberProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $contractNumber = (string) ($user->contract_number ?: ($user->username ?: 'TEST-100001'));

        try {
            $subscriber = $this->subscriberProvider->findSubscriberByContract($contractNumber);
            $subscription = $this->subscriberProvider->getSubscription($contractNumber);
            $usage = $this->subscriberProvider->getUsage($contractNumber);
            $borrowing = $this->subscriberProvider->getBorrowingStatus($contractNumber);
        } catch (\Throwable $e) {
            $subscriber = null;
            $subscription = null;
            $usage = null;
            $borrowing = null;
        }

        $packages = $this->subscriberProvider->listAvailablePackages();
        $complaints = Complaint::where('user_id', $user->id)
            ->orWhere('contract_number', $contractNumber)
            ->orderByDesc('created_at')
            ->get();

        $payments = Payment::where('user_id', $user->id)
            ->orWhere('contract_number', $contractNumber)
            ->orderByDesc('created_at')
            ->get();

        return view('subscriber.dashboard', compact(
            'user',
            'contractNumber',
            'subscriber',
            'subscription',
            'usage',
            'borrowing',
            'packages',
            'complaints',
            'payments'
        ));
    }

    public function recharge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        $contractNumber = (string) ($user->contract_number ?: ($user->username ?: 'TEST-100001'));

        try {
            $result = $this->subscriberProvider->recharge($contractNumber, $validated['package_id']);

            return back()->with('status', $result->message ?? 'تم شحن الحساب بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function activateBorrowing(Request $request): RedirectResponse
    {
        $user = $request->user();
        $contractNumber = (string) ($user->contract_number ?: ($user->username ?: 'TEST-100001'));

        try {
            $this->subscriberProvider->activateEmergencyBorrowing($contractNumber);

            return back()->with('status', 'تم تفعيل سلفة الطوارئ بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
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
        $contractNumber = (string) ($user->contract_number ?: ($user->username ?: 'TEST-100001'));

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
