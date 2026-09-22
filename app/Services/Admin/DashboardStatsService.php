<?php

namespace App\Services\Admin;

use App\Enums\RecordStatus;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Package;
use App\Models\RechargeRequest;
use App\Models\Role;
use App\Models\User;

class DashboardStatsService
{
    public function getStats(): array
    {
        $staffRoleIds = Role::whereIn('name', User::STAFF_ROLES)->pluck('id');

        return [
            'total_subscribers' => User::where('type', 'subscriber')->count(),
            'active_subscribers' => User::where('type', 'subscriber')
                ->where('status', UserStatus::Active)
                ->count(),
            'pending_recharges' => RechargeRequest::where('status', 'pending')->count(),
            'open_complaints' => Complaint::whereIn('status', ['open', 'in_progress'])->count(),
            'total_employees' => User::whereHas('roles', fn ($q) => $q->whereIn('roles.id', $staffRoleIds))->count(),
            'active_packages' => Package::where('status', RecordStatus::Active)->count(),
        ];
    }
}
