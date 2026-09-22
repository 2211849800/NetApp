<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function __construct(
        private readonly RoleRedirectService $roleRedirectService,
    ) {}

    /**
     * Attempt web session authentication for internal staff users.
     *
     * @throws ValidationException
     */
    public function attemptLogin(string $login, string $password, bool $remember = false): User
    {
        $throttleKey = $this->throttleKey($login);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $user = User::query()
            ->where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'login' => __('Invalid credentials.'),
            ]);
        }

        $user->load('roles');

        if (! $user->canLogin()) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'login' => __('Your account is not active.'),
            ]);
        }

        if (! $user->isStaff()) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'login' => __('Invalid credentials.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $remember);
        $user->load('roles.permissions');

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }

    public function dashboardRouteFor(User $user): string
    {
        return $this->roleRedirectService->dashboardRouteFor($user);
    }

    private function throttleKey(string $login): string
    {
        return Str::transliterate(Str::lower($login).'|'.request()->ip());
    }
}
