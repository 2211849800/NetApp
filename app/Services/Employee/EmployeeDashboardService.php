<?php

namespace App\Services\Employee;

use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\RechargeRequest;
use App\Models\SubscriptionRequest;

class EmployeeDashboardService
{
    /**
     * @return array{pending_recharges: int, open_tickets: int, completed_today: int, pending_subscriptions: int, stale_recharges: int, stale_tickets: int}
     */
    public function getStats(int $employeeId): array
    {
        return [
            'pending_recharges' => RechargeRequest::whereIn('status', ['pending', 'under_review'])->count(),
            'open_tickets' => Complaint::whereIn('status', ['open', 'new', 'under_review', 'in_progress'])->count(),
            'pending_subscriptions' => SubscriptionRequest::whereIn('status', ['pending', 'under_review'])->count(),
            'completed_today' => AuditLog::query()
                ->whereDate('created_at', now()->toDateString())
                ->where('user_id', $employeeId)
                ->where('result', 'success')
                ->count(),
            'stale_recharges' => RechargeRequest::whereIn('status', ['pending', 'under_review'])
                ->where('created_at', '<', now()->subHours(24))->count(),
            'stale_tickets' => Complaint::whereIn('status', ['open', 'new', 'under_review', 'in_progress'])
                ->where('created_at', '<', now()->subHours(48))->count(),
        ];
    }
}
