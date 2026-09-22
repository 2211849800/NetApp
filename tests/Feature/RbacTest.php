<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    // ──────────────────────────────────────────────
    // Role Assignment
    // ──────────────────────────────────────────────

    public function test_user_can_be_assigned_a_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->assertTrue($user->hasRole('employee'));
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $user->assignRole('supervisor');

        $this->assertTrue($user->hasRole('employee'));
        $this->assertTrue($user->hasRole('supervisor'));
    }

    public function test_user_can_have_role_removed(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $user->removeRole('employee');

        $this->assertFalse($user->fresh()->hasRole('employee'));
    }

    public function test_has_any_role_check(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->assertTrue($user->hasAnyRole(['employee', 'admin']));
        $this->assertFalse($user->hasAnyRole(['admin', 'supervisor']));
    }

    // ──────────────────────────────────────────────
    // Permission Checks
    // ──────────────────────────────────────────────

    public function test_employee_has_assigned_permissions(): void
    {
        $user = User::factory()->employee()->create();
        $user->assignRole('employee');
        $user->load('roles.permissions');

        $this->assertTrue($user->hasPermission('view_recharges'));
        $this->assertTrue($user->hasPermission('view_complaints'));
        $this->assertTrue($user->hasPermission('manage_complaints'));
    }

    public function test_employee_does_not_have_admin_permissions(): void
    {
        $user = User::factory()->employee()->create();
        $user->assignRole('employee');
        $user->load('roles.permissions');

        $this->assertFalse($user->hasPermission('manage_employees'));
        $this->assertFalse($user->hasPermission('manage_roles'));
        $this->assertFalse($user->hasPermission('view_audit_logs'));
    }

    public function test_admin_has_all_permissions(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('admin');
        $user->load('roles.permissions');

        // Admin type automatically gets all permissions
        $this->assertTrue($user->hasPermission('manage_employees'));
        $this->assertTrue($user->hasPermission('manage_roles'));
        $this->assertTrue($user->hasPermission('view_audit_logs'));
        $this->assertTrue($user->hasPermission('any_permission_at_all'));
    }

    public function test_get_all_permission_names(): void
    {
        $user = User::factory()->employee()->create();
        $user->assignRole('employee');
        $user->load('roles.permissions');

        $permissions = $user->getAllPermissionNames();
        $this->assertIsArray($permissions);
        $this->assertContains('view_recharges', $permissions);
    }

    // ──────────────────────────────────────────────
    // Permission Middleware
    // ──────────────────────────────────────────────

    public function test_permission_middleware_blocks_unauthorized_user(): void
    {
        // Create a route that requires manage_employees permission
        \Illuminate\Support\Facades\Route::middleware(['auth:sanctum', 'permission:manage_employees'])
            ->get('/api/v1/test-permission', fn () => response()->json(['ok' => true]));

        $user = User::factory()->employee()->create();
        $user->assignRole('employee'); // employees don't have manage_employees

        $response = $this->actingAs($user)->getJson('/api/v1/test-permission');
        $response->assertStatus(403);
    }

    public function test_permission_middleware_allows_authorized_user(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['auth:sanctum', 'permission:view_complaints'])
            ->get('/api/v1/test-permission', fn () => response()->json(['ok' => true]));

        $user = User::factory()->employee()->create();
        $user->assignRole('employee'); // employees have view_complaints

        $response = $this->actingAs($user)->getJson('/api/v1/test-permission');
        $response->assertStatus(200);
    }

    public function test_permission_middleware_allows_admin_for_any_permission(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['auth:sanctum', 'permission:manage_employees'])
            ->get('/api/v1/test-permission', fn () => response()->json(['ok' => true]));

        $user = User::factory()->admin()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->getJson('/api/v1/test-permission');
        $response->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // User Type Middleware
    // ──────────────────────────────────────────────

    public function test_user_type_middleware_allows_correct_type(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['auth:sanctum', 'user.type:subscriber'])
            ->get('/api/v1/test-type', fn () => response()->json(['ok' => true]));

        $user = User::factory()->subscriber()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/test-type');
        $response->assertStatus(200);
    }

    public function test_user_type_middleware_blocks_wrong_type(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['auth:sanctum', 'user.type:subscriber'])
            ->get('/api/v1/test-type', fn () => response()->json(['ok' => true]));

        $user = User::factory()->employee()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/test-type');
        $response->assertStatus(403);
    }

    public function test_user_type_middleware_allows_multiple_types(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['auth:sanctum', 'user.type:employee,admin'])
            ->get('/api/v1/test-type', fn () => response()->json(['ok' => true]));

        $employee = User::factory()->employee()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($employee)->getJson('/api/v1/test-type')->assertStatus(200);
        $this->actingAs($admin)->getJson('/api/v1/test-type')->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Role Seeder Verification
    // ──────────────────────────────────────────────

    public function test_seeder_creates_expected_roles(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'supervisor']);
        $this->assertDatabaseHas('roles', ['name' => 'employee']);
        $this->assertDatabaseHas('roles', ['name' => 'subscriber']);
    }

    public function test_seeder_creates_expected_permissions(): void
    {
        $this->assertDatabaseHas('permissions', ['name' => 'view_recharges']);
        $this->assertDatabaseHas('permissions', ['name' => 'approve_recharges']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_packages']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_employees']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_audit_logs']);
    }

    public function test_system_roles_are_marked_as_system(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $this->assertTrue($adminRole->is_system);
    }
}
