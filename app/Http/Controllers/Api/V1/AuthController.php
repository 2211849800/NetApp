<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new subscriber account.
     *
     * Only subscribers can self-register.
     * Employees and admins are created by admins through admin endpoints.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'type' => 'subscriber',
        ]);

        $user->assignRole('subscriber');

        $token = $user->createToken(
            'subscriber-app',
            ['subscriber'],
        )->plainTextToken;

        $user->load('roles.permissions');

        return $this->createdResponse([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Registration successful.');
    }

    /**
     * Authenticate a user and issue a Sanctum token.
     *
     * Works for all user types (subscriber, employee, admin).
     * Token abilities are set based on user type for an additional layer of safety.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                'The provided credentials are incorrect.',
                401,
                'INVALID_CREDENTIALS',
            );
        }

        if (! $user->canLogin()) {
            return $this->errorResponse(
                'Your account is not active.',
                403,
                'ACCOUNT_INACTIVE',
            );
        }

        // Token abilities match user type for defense-in-depth
        $abilities = [$user->type];

        $deviceName = $request->input('device_name', $user->type . '-app');

        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        $user->load('roles.permissions');

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Login successful.');
    }

    /**
     * Revoke the current access token (logout).
     */
    public function logout(): JsonResponse
    {
        $user = request()->user();
        
        $token = $user->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->successResponse(message: 'Logged out successfully.');
    }

    /**
     * Get the authenticated user's profile with roles and permissions.
     */
    public function me(): JsonResponse
    {
        $user = request()->user();
        $user->load('roles.permissions');

        return $this->successResponse([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'password' => $request->password,
        ]);

        return $this->successResponse(message: 'Password changed successfully.');
    }
}
