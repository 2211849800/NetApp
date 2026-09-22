<?php

namespace App\Services\Staff;

use App\Models\Complaint;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;

class ComplaintService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    public function create(array $data, User $actor): Complaint
    {
        $complaint = Complaint::create([
            'user_id' => $data['user_id'] ?? null,
            'contract_number' => $data['contract_number'] ?? null,
            'subscriber_name' => $data['subscriber_name'] ?? null,
            'subject' => $data['subject'],
            'type' => $data['type'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'open',
            'internal_notes' => $data['internal_notes'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? $actor->id,
        ]);

        $this->auditLog->log('COMPLAINT_CREATED', 'Complaint', $complaint->id, null, [
            'subject' => $complaint->subject,
            'status' => $complaint->status,
            'contract_number' => $complaint->contract_number,
        ], actor: $actor);

        return $complaint;
    }

    public function update(Complaint $complaint, array $data, User $actor): Complaint
    {
        return DB::transaction(function () use ($complaint, $data, $actor) {
            $old = $complaint->only(['status', 'internal_notes', 'staff_reply', 'assigned_to']);

            $payload = [
                'status' => $data['status'] ?? $complaint->status,
                'internal_notes' => $data['internal_notes'] ?? $complaint->internal_notes,
                'staff_reply' => $data['staff_reply'] ?? $complaint->staff_reply,
                'assigned_to' => $data['assigned_to'] ?? $complaint->assigned_to,
                'priority' => $data['priority'] ?? $complaint->priority,
            ];

            if (in_array($payload['status'], ['resolved', 'closed'], true) && ! $complaint->resolved_at) {
                $payload['resolved_at'] = now();
            }

            if (! in_array($payload['status'], ['resolved', 'closed'], true)) {
                $payload['resolved_at'] = null;
            }

            $complaint->update($payload);

            $action = match ($payload['status']) {
                'resolved' => 'COMPLAINT_RESOLVED',
                'closed' => 'COMPLAINT_CLOSED',
                'in_progress' => 'COMPLAINT_IN_PROGRESS',
                default => 'COMPLAINT_UPDATED',
            };

            $this->auditLog->log($action, 'Complaint', $complaint->id, $old, $payload, actor: $actor);

            return $complaint->fresh(['user', 'assignee']);
        });
    }
}
