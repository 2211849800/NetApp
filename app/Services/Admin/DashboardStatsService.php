<?php

namespace App\Services\Admin;

use App\Enums\RecordStatus;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Package;
use App\Models\RechargeRequest;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;

class DashboardStatsService
{
    public function getStats(): array
    {
        $staffRoleIds = Role::whereIn('name', User::STAFF_ROLES)->pluck('id');
        $subscriberQuery = User::where('type', 'subscriber');
        $activeSubscribers = (clone $subscriberQuery)->where('status', UserStatus::Active)->count();
        $totalSubscribers = (clone $subscriberQuery)->count();
        $pendingRechargeQuery = RechargeRequest::where('status', 'pending');
        $pendingRecharges = (clone $pendingRechargeQuery)->count();
        $openComplaints = Complaint::whereIn('status', ['open', 'in_progress'])->count();
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
