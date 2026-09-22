<?php

namespace App\Services\Auth;

use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleRedirectService
{
    /**
     * Resolve the dashboard route for an authenticated staff user.
     */
    public function dashboardRouteFor(User $user): string
    {
        $user->loadMissing('roles');

        return match ($user->primaryStaffRole()) {
            'admin' => route('admin.dashboard'),
            'supervisor' => route('supervisor.dashboard'),
            'employee' => route('employee.dashboard'),
            default => throw new AccessDeniedHttpException('No dashboard available for this account.'),
        };
    }
}
