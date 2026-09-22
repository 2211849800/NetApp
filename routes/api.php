<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SubscriberApiController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes are prefixed with /api/v1/ via the route group below.
| Authentication is handled by Laravel Sanctum.
|
| Route organization:
|   1. Public routes (no auth required)
|   2. Authenticated routes (any user type)
|   3. Subscriber-only routes
|   4. Staff routes (employee + admin)
|   5. Admin-only routes
|   6. Webhook routes
|
*/

Route::prefix('v1')->group(function () {

    // ──────────────────────────────────────────────
    // Public Routes
    // ──────────────────────────────────────────────

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1')
            ->name('auth.register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('auth.login');
    });

    // ──────────────────────────────────────────────
    // Authenticated Routes (any user type)
    // ──────────────────────────────────────────────

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

        // ──────────────────────────────────────────
        // Subscriber Routes
        // ──────────────────────────────────────────

        Route::middleware('user.type:subscriber')->prefix('subscriber')->group(function () {
            Route::get('/profile', [SubscriberApiController::class, 'profile'])->name('api.subscriber.profile');
            Route::get('/subscription', [SubscriberApiController::class, 'subscription'])->name('api.subscriber.subscription');
            Route::get('/usage', [SubscriberApiController::class, 'usage'])->name('api.subscriber.usage');
            Route::get('/borrowing', [SubscriberApiController::class, 'borrowing'])->name('api.subscriber.borrowing');
            Route::post('/recharge', [SubscriberApiController::class, 'recharge'])->name('api.subscriber.recharge');
            Route::post('/change-package', [SubscriberApiController::class, 'changePackage'])->name('api.subscriber.change-package');
            Route::post('/borrow', [SubscriberApiController::class, 'activateBorrowing'])->name('api.subscriber.borrow');
        });


        // ──────────────────────────────────────────
        // Staff Routes (Employee + Admin)
        // ──────────────────────────────────────────

        Route::middleware('user.type:employee,admin')->prefix('admin')->group(function () {
            // Admin/Employee routes will be added in Phase 2+
        });

        // ──────────────────────────────────────────
        // Subscriber Management API (via Provider)
        // ──────────────────────────────────────────

        Route::prefix('subscribers')->group(function () {
            Route::get('/{contractNumber}', [SubscriberApiController::class, 'show'])->name('api.subscribers.show');
            Route::get('/{contractNumber}/subscription', [SubscriberApiController::class, 'subscription'])->name('api.subscribers.subscription');
            Route::get('/{contractNumber}/usage', [SubscriberApiController::class, 'usage'])->name('api.subscribers.usage');
            Route::get('/{contractNumber}/borrowing', [SubscriberApiController::class, 'borrowing'])->name('api.subscribers.borrowing');
            Route::post('/{contractNumber}/recharge', [SubscriberApiController::class, 'recharge'])->name('api.subscribers.recharge');
            Route::post('/{contractNumber}/change-package', [SubscriberApiController::class, 'changePackage'])->name('api.subscribers.change-package');
        });

    });

    // ──────────────────────────────────────────────
    // Webhook Routes (Payment Gateways & Simulator)
    // ──────────────────────────────────────────────

    Route::prefix('webhooks')->group(function () {
        Route::post('/lypay', [App\Http\Controllers\Api\V1\WebhookController::class, 'handleLypay'])->name('webhooks.lypay');
        Route::post('/onepay', [App\Http\Controllers\Api\V1\WebhookController::class, 'handleOnePay'])->name('webhooks.onepay');
        Route::post('/simulate', [App\Http\Controllers\Api\V1\WebhookController::class, 'simulate'])->name('webhooks.simulate');
    });
});

