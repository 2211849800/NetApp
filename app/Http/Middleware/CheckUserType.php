<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict routes by user type.
 *
 * Usage in routes:
 *   ->middleware('user.type:subscriber')
 *   ->middleware('user.type:employee,admin')  // allows either type
 */
class CheckUserType
{
    use ApiResponse;

    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        if (! in_array($user->type, $types)) {
            return $this->forbiddenResponse('Access restricted to your user type.');
        }

        return $next($request);
    }
}
