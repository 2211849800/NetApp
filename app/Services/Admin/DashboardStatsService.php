<?php

namespace App\Services\Admin;

use App\Enums\RecordStatus;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Package;
use App\Models\Payment;
use App\Models\RechargeRequest;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;

class DashboardStatsService
{
    public function getStats(int $days = 7): array
    {
        $periodStart = CarbonImmutable::today()->subDays($days - 1);
        $previousPeriodStart = $periodStart->subDays($days);
        $periodEnd = CarbonImmutable::tomorrow();
        $staffRoleIds = Role::whereIn('name', User::STAFF_ROLES)->pluck('id');
        $subscriberQuery = User::where('type', 'subscriber');
        $activeSubscribers = (clone $subscriberQuery)->where('status', UserStatus::Active)->count();
        $totalSubscribers = (clone $subscriberQuery)->count();
        $pendingRechargeQuery = RechargeRequest::whereIn('status', ['pending', 'under_review']);
        $pendingRecharges = (clone $pendingRechargeQuery)->count();
        $openComplaints = Complaint::whereIn('status', ['open', 'new', 'under_review', 'in_progress'])->count();
        $staleComplaints = Complaint::whereIn('status', ['open', 'new', 'under_review', 'in_progress'])
            ->where('created_at', '<', now()->subHours(48))->count();
        $totalComplaints = Complaint::count();
        $staffQuery = User::whereHas('roles', fn ($q) => $q->whereIn('roles.id', $staffRoleIds));
        $totalEmployees = (clone $staffQuery)->count();
        $activeEmployees = (clone $staffQuery)->where('status', UserStatus::Active)->count();
        $activePackages = Package::where('status', RecordStatus::Active)->count();
        $totalPackages = Package::count();

        return [
            'total_subscribers' => $totalSubscribers,
            'active_subscribers' => $activeSubscribers,
            'new_subscribers_this_week' => (clone $subscriberQuery)->where('created_at', '>=', now()->startOfWeek())->count(),
            'subscribers_by_day' => $this->dailyCounts(clone $subscriberQuery),
            'active_subscribers_by_day' => $this->dailyCounts((clone $subscriberQuery)->where('status', UserStatus::Active)),
            'pending_recharges' => $pendingRecharges,
            'pending_recharges_by_day' => $this->dailyCounts(clone $pendingRechargeQuery),
            'open_complaints' => $openComplaints,
            'total_complaints' => $totalComplaints,
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'active_packages' => $activePackages,
            'total_packages' => $totalPackages,
            'period_days' => $days,
            'new_subscribers_period' => (clone $subscriberQuery)->where('created_at', '>=', $periodStart)->where('created_at', '<', $periodEnd)->count(),
            'new_subscribers_previous_period' => (clone $subscriberQuery)->where('created_at', '>=', $previousPeriodStart)->where('created_at', '<', $periodStart)->count(),
            'revenue_period' => Payment::where('status', 'completed')->where('paid_at', '>=', $periodStart)->where('paid_at', '<', $periodEnd)->sum('amount'),
            'revenue_previous_period' => Payment::where('status', 'completed')->where('paid_at', '>=', $previousPeriodStart)->where('paid_at', '<', $periodStart)->sum('amount'),
            'stale_recharges' => RechargeRequest::whereIn('status', ['pending', 'under_review'])->where('created_at', '<', now()->subHours(24))->count(),
            'stale_complaints' => $staleComplaints,
        ];
    }

    /**
     * Count records created on each of the past seven calendar days.
     *
     * @return list<int>
     */
    private function dailyCounts($query): array
    {
        $start = CarbonImmutable::today()->subDays(6);
        $counts = $query
            ->where('created_at', '>=', $start)
            ->pluck('created_at')
            ->map(fn ($createdAt) => CarbonImmutable::parse($createdAt)->toDateString())
            ->countBy();

        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => $counts->get($start->addDays(6 - $daysAgo)->toDateString(), 0))
            ->all();
    }
}
