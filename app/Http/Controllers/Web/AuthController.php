<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LoginRequest;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->authenticationService->attemptLogin(
            $request->validated('login'),
            $request->validated('password'),
            $request->boolean('remember'),
        );

        $request->session()->regenerate();

        return redirect()->intended(
            $this->authenticationService->dashboardRouteFor($user)
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authenticationService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
