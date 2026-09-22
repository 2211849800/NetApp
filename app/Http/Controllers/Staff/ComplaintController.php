<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Services\Staff\ComplaintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function __construct(
        private readonly ComplaintService $service,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $complaints = Complaint::query()
            ->with(['user', 'assignee'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->input('q');
                $q->where(function ($inner) use ($term) {
                    $inner->where('subject', 'like', "%{$term}%")
                        ->orWhere('subscriber_name', 'like', "%{$term}%")
                        ->orWhere('contract_number', 'like', "%{$term}%");
                });
            })
            ->orderByRaw("CASE WHEN status IN ('open', 'in_progress') THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $subscribers = User::subscribers()->orderBy('name')->get(['id', 'name', 'username', 'contract_number', 'phone']);

        return view('staff.complaints.index', compact('complaints', 'subscribers', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'contract_number' => ['nullable', 'string', 'max:50'],
            'subscriber_name' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ], [
            'subject.required' => 'موضوع الشكوى مطلوب.',
        ]);

        if (! empty($validated['user_id']) && empty($validated['subscriber_name'])) {
            $user = User::find($validated['user_id']);
            $validated['subscriber_name'] = $user?->name;
            $validated['contract_number'] = $validated['contract_number'] ?: $user?->contract_number;
        }

        $this->service->create($validated, $request->user());

        return back()->with('status', 'تم إنشاء التذكرة بنجاح.');
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['user', 'assignee']);
        $staff = User::staff()->orderBy('name')->get(['id', 'name']);

        return view('staff.complaints.show', compact('complaint', 'staff'));
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'staff_reply' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $this->service->update($complaint, $validated, $request->user());

        return back()->with('status', 'تم تحديث الشكوى بنجاح.');
    }
}
