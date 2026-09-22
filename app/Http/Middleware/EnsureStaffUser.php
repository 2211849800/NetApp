<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the authenticated user is internal staff (admin, employee, or supervisor).
 */
class EnsureStaffUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $user->loadMissing('roles');

        if (! $user->isStaff()) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
