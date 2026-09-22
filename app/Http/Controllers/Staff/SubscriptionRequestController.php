<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionRequest;
use App\Services\Staff\SubscriptionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionRequestController extends Controller
{
    public function __construct(
        private readonly SubscriptionRequestService $service,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $requests = SubscriptionRequest::query()
            ->with(['user', 'processor'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE WHEN status IN ('pending', 'under_review') THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('staff.subscription-requests.index', compact('requests', 'status'));
    }

    public function show(SubscriptionRequest $subscriptionRequest): View
    {
        $subscriptionRequest->load(['user', 'processor']);

        return view('staff.subscription-requests.show', ['requestItem' => $subscriptionRequest]);
    }

    public function approve(Request $request, SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $validated = $request->validate([
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $result = $this->service->approve(
                $subscriptionRequest,
                $request->user(),
                $validated['internal_notes'] ?? null,
            );

            return redirect()
                ->route('staff.subscription-requests.index')
                ->with('status', sprintf(
                    'تم قبول الطلب وإنشاء حساب المشترك. رقم العقد: %s — اسم المستخدم: %s — كلمة المرور المؤقتة: %s',
                    $result['contract_number'],
                    $result['user']->username,
                    $result['password'],
                ));
        } catch (\Throwable $e) {
            return back()->with('error', $this->service->actionErrorMessage($e));
        }
    }

    public function reject(Request $request, SubscriptionRequest $subscriptionRequest): RedirectResponse
    {
        $validated = $request->validate([
            'internal_notes' => ['required', 'string', 'max:2000'],
        ], [
            'internal_notes.required' => 'يرجى تدوين سبب الرفض.',
        ]);

        try {
            $this->service->reject($subscriptionRequest, $request->user(), $validated['internal_notes']);

            return redirect()
                ->route('staff.subscription-requests.index')
                ->with('status', 'تم رفض طلب الاشتراك.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->service->actionErrorMessage($e));
        }
    }
}
