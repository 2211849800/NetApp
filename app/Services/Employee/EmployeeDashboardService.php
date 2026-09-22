<?php

namespace App\Services\Employee;

use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\RechargeRequest;
use App\Models\SubscriptionRequest;

class EmployeeDashboardService
{
    /**
     * @return array{pending_recharges: int, open_tickets: int, completed_today: int, pending_subscriptions: int}
     */
    public function getStats(): array
    {
        return [
            'pending_recharges' => RechargeRequest::where('status', 'pending')->count(),
            'open_tickets' => Complaint::whereIn('status', ['open', 'in_progress'])->count(),
            'pending_subscriptions' => SubscriptionRequest::whereIn('status', ['pending', 'under_review'])->count(),
            'completed_today' => AuditLog::query()
                ->whereDate('created_at', now()->toDateString())
                ->where('result', 'success')
                ->count(),
        ];
    }
}
