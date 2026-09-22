<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce permission-based access control.
 *
 * Usage in routes:
 *   ->middleware('permission:manage_packages')
 *   ->middleware('permission:view_recharges,approve_recharges')  // requires ANY of these
 */
class CheckPermission
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->denyUnauthenticated($request);
        }

        if (! $user->relationLoaded('roles')) {
            $user->load('roles.permissions');
        }

        if (! $user->hasAnyPermission($permissions)) {
            return $this->denyForbidden($request);
        }

        return $next($request);
    }

    private function denyUnauthenticated(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->unauthorizedResponse();
        }

        return redirect()->route('login');
    }

    private function denyForbidden(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->forbiddenResponse('You do not have permission to perform this action.');
        }

        abort(403, 'You do not have permission to perform this action.');
    }
}
