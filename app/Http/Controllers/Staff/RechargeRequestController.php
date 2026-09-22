<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\RechargeRequest;
use App\Services\Staff\RechargeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RechargeRequestController extends Controller
{
    public function __construct(
        private readonly RechargeRequestService $service,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $requests = RechargeRequest::query()
            ->with(['user', 'processor'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => RechargeRequest::count(),
            'pending' => RechargeRequest::where('status', 'pending')->count(),
            'approved' => RechargeRequest::where('status', 'approved')->count(),
            'rejected' => RechargeRequest::where('status', 'rejected')->count(),
        ];

        return view('staff.recharges.index', compact('requests', 'status', 'counts'));
    }

    public function show(RechargeRequest $rechargeRequest): View
    {
        $rechargeRequest->load(['user', 'processor']);

        return view('staff.recharges.show', ['requestItem' => $rechargeRequest]);
    }

    public function approve(Request $request, RechargeRequest $rechargeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->service->approve($rechargeRequest, $request->user(), $validated['notes'] ?? null);

            return redirect()
                ->route('staff.recharges.index')
                ->with('status', 'تم قبول طلب الشحن وتحديث حالة المشترك.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->service->actionErrorMessage($e));
        }
    }

    public function reject(Request $request, RechargeRequest $rechargeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:2000'],
        ], [
            'notes.required' => 'يرجى تدوين سبب الرفض.',
        ]);

        try {
            $this->service->reject($rechargeRequest, $request->user(), $validated['notes']);

            return redirect()
                ->route('staff.recharges.index')
                ->with('status', 'تم رفض طلب الشحن.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->service->actionErrorMessage($e));
        }
    }
}
