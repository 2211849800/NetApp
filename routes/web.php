<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\IptvPackageController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Staff\ComplaintController;
use App\Http\Controllers\Staff\PaymentController;
use App\Http\Controllers\Staff\RechargeRequestController;
use App\Http\Controllers\Staff\SubscriberController;
use App\Http\Controllers\Staff\SubscriptionRequestController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Web\AuthController;
use App\Services\Auth\RoleRedirectService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — ISP Customer Services Platform
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect(app(RoleRedirectService::class)->dashboardRouteFor(auth()->user()))
        : redirect()->route('login');
});

// ──────────────────────────────────────────────
// Guest Authentication
// ──────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ──────────────────────────────────────────────
// Authenticated staff redirect helper
// ──────────────────────────────────────────────

Route::middleware(['auth', 'staff'])->get('/dashboard', function () {
    return redirect(app(RoleRedirectService::class)->dashboardRouteFor(auth()->user()));
})->name('dashboard.redirect');

// ──────────────────────────────────────────────
// Admin Routes
// ──────────────────────────────────────────────

Route::middleware(['auth', 'staff', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Packages Management
        Route::middleware('permission:manage_packages')->group(function () {
            Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
            Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
            Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
            Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');
            Route::get('/packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
            Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
            Route::patch('/packages/{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
        });

        // Products Management
        Route::middleware('permission:manage_products')->group(function () {
            Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        });

        // Offers Management
        Route::middleware('permission:manage_offers')->group(function () {
            Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
            Route::get('/offers/create', [OfferController::class, 'create'])->name('offers.create');
            Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
            Route::get('/offers/{offer}', [OfferController::class, 'show'])->name('offers.show');
            Route::get('/offers/{offer}/edit', [OfferController::class, 'edit'])->name('offers.edit');
            Route::put('/offers/{offer}', [OfferController::class, 'update'])->name('offers.update');
            Route::patch('/offers/{offer}/toggle-status', [OfferController::class, 'toggleStatus'])->name('offers.toggle-status');
        });

        // IPTV Packages Management
        Route::middleware('permission:manage_packages')->group(function () {
            Route::get('/iptv-packages', [IptvPackageController::class, 'index'])->name('iptv-packages.index');
            Route::get('/iptv-packages/create', [IptvPackageController::class, 'create'])->name('iptv-packages.create');
            Route::post('/iptv-packages', [IptvPackageController::class, 'store'])->name('iptv-packages.store');
            Route::get('/iptv-packages/{iptvPackage}/edit', [IptvPackageController::class, 'edit'])->name('iptv-packages.edit');
            Route::put('/iptv-packages/{iptvPackage}', [IptvPackageController::class, 'update'])->name('iptv-packages.update');
            Route::patch('/iptv-packages/{iptvPackage}/toggle-status', [IptvPackageController::class, 'toggleStatus'])->name('iptv-packages.toggle-status');
        });

        // Bank Accounts Management
        Route::middleware('permission:manage_bank_accounts')->group(function () {
            Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');
            Route::get('/bank-accounts/create', [BankAccountController::class, 'create'])->name('bank-accounts.create');
            Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
            Route::get('/bank-accounts/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('bank-accounts.edit');
            Route::put('/bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
            Route::patch('/bank-accounts/{bankAccount}/toggle-status', [BankAccountController::class, 'toggleStatus'])->name('bank-accounts.toggle-status');
        });

        // Employees Management
        Route::middleware('permission:manage_employees')->group(function () {
            Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
            Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
            Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
            Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
            Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
            Route::get('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPasswordForm'])->name('employees.reset-password-form');
            Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
            Route::patch('/employees/{employee}/activate', [EmployeeController::class, 'activate'])->name('employees.activate');
            Route::patch('/employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');
            Route::patch('/employees/{employee}/suspend', [EmployeeController::class, 'suspend'])->name('employees.suspend');
        });

        // Roles & Permissions Management
        Route::middleware('permission:manage_roles')->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        });

        // Audit Logs
        Route::middleware('permission:view_audit_logs')->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        });

        // System Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('settings.update');

        // Admin Profile
        Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [AdminProfileController::class, 'changePassword'])->name('profile.change-password');
    });


// ──────────────────────────────────────────────
// Employee Routes
// ──────────────────────────────────────────────

Route::middleware(['auth', 'staff', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    });

// ──────────────────────────────────────────────
// Subscriber Routes
// ──────────────────────────────────────────────

Route::middleware(['auth', 'role:subscriber'])
    ->prefix('subscriber')
    ->name('subscriber.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Subscriber\DashboardController::class, 'index'])->name('dashboard');
        Route::post('/recharge', [\App\Http\Controllers\Subscriber\DashboardController::class, 'recharge'])->name('recharge.submit');
        Route::post('/borrow', [\App\Http\Controllers\Subscriber\DashboardController::class, 'activateBorrowing'])->name('borrow.submit');
        Route::post('/complaints', [\App\Http\Controllers\Subscriber\DashboardController::class, 'storeComplaint'])->name('complaints.submit');
    });

// ──────────────────────────────────────────────
// Supervisor Routes
// ──────────────────────────────────────────────


Route::middleware(['auth', 'staff', 'role:supervisor'])
    ->prefix('supervisor')
    ->name('supervisor.')
    ->group(function () {
        Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
    });

// ──────────────────────────────────────────────
// Shared Staff Routes (permission-protected)
// ──────────────────────────────────────────────

Route::middleware(['auth', 'staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/my-balance', [App\Http\Controllers\Staff\MyBalanceController::class, 'index'])->name('my-balance.index');
        Route::post('/my-balance/entries', [App\Http\Controllers\Staff\MyBalanceController::class, 'addEntry'])->name('my-balance.entries.store');
        Route::post('/my-balance/close', [App\Http\Controllers\Staff\MyBalanceController::class, 'closeAccount'])->name('my-balance.close');

        Route::middleware('permission:view_subscribers')->group(function () {

            Route::get('/subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
            Route::post('/subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');
            Route::post('/subscribers/recharge', [SubscriberController::class, 'recharge'])->name('subscribers.recharge');
            Route::post('/subscribers/change-package', [SubscriberController::class, 'changePackage'])->name('subscribers.change-package');
            Route::post('/subscribers/activate-borrowing', [SubscriberController::class, 'activateBorrowing'])->name('subscribers.activate-borrowing');
        });

        Route::middleware('permission:view_complaints')->group(function () {
            Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
            Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
            Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
            Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])
                ->middleware('permission:manage_complaints')
                ->name('complaints.update');
        });

        Route::get('/payments', [PaymentController::class, 'index'])
            ->middleware('permission:view_payments')
            ->name('payments.index');
        Route::post('/payments/simulate', [App\Http\Controllers\Api\V1\WebhookController::class, 'simulate'])
            ->middleware('permission:view_payments')
            ->name('payments.simulate');


        Route::middleware('permission:view_recharges')->group(function () {
            Route::get('/recharges', [RechargeRequestController::class, 'index'])->name('recharges.index');
            Route::get('/recharges/{rechargeRequest}', [RechargeRequestController::class, 'show'])->name('recharges.show');
            Route::post('/recharges/{rechargeRequest}/approve', [RechargeRequestController::class, 'approve'])
                ->middleware('permission:approve_recharges')
                ->name('recharges.approve');
            Route::post('/recharges/{rechargeRequest}/reject', [RechargeRequestController::class, 'reject'])
                ->middleware('permission:reject_recharges')
                ->name('recharges.reject');
        });

        Route::middleware('permission:view_subscription_requests')->group(function () {
            Route::get('/subscription-requests', [SubscriptionRequestController::class, 'index'])->name('subscription-requests.index');
            Route::get('/subscription-requests/{subscriptionRequest}', [SubscriptionRequestController::class, 'show'])->name('subscription-requests.show');
            Route::post('/subscription-requests/{subscriptionRequest}/approve', [SubscriptionRequestController::class, 'approve'])
                ->middleware('permission:manage_subscription_requests')
                ->name('subscription-requests.approve');
            Route::post('/subscription-requests/{subscriptionRequest}/reject', [SubscriptionRequestController::class, 'reject'])
                ->middleware('permission:manage_subscription_requests')
                ->name('subscription-requests.reject');
        });
    });

// Legacy mockup routes — redirect to new paths
Route::redirect('/subscribers', '/staff/subscribers');
Route::redirect('/packages', '/admin/packages');
Route::redirect('/complaints', '/staff/complaints');
Route::redirect('/payments', '/staff/payments');

Route::middleware(['auth', 'staff'])->get('/mobile-app-demo', [DashboardController::class, 'mobileDemo'])
    ->name('mobile.demo');
