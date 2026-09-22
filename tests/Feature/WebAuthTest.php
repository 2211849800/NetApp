<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);
    }

    private function adminUser(): User
    {
        return User::where('email', 'admin@netapp.com')->firstOrFail();
    }

    private function staffUser(string $role): User
    {
        if ($role === 'admin') {
            return $this->adminUser();
        }

        return User::where('email', "{$role}@example.com")->firstOrFail();
    }

    public function test_staff_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminUser());
    }

    public function test_staff_can_login_with_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@netapp.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->adminUser());
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'login' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->staffUser('employee');
        $user->update(['status' => UserStatus::Inactive, 'is_active' => false]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'employee',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Your account is not active.']);
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = $this->staffUser('supervisor');
        $user->update(['status' => UserStatus::Suspended, 'is_active' => false]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'supervisor',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_employee_is_redirected_to_employee_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'employee',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('employee.dashboard'));
    }

    public function test_supervisor_is_redirected_to_supervisor_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'supervisor',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('supervisor.dashboard'));
    }

    public function test_employee_cannot_access_admin_routes(): void
    {
        $employee = $this->staffUser('employee');

        $this->actingAs($employee)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_supervisor_cannot_access_unauthorized_admin_routes(): void
    {
        $supervisor = $this->staffUser('supervisor');

        $this->actingAs($supervisor)
            ->get(route('admin.packages.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_authenticated_routes(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('employee.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_logout_works_correctly(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_session_is_invalidated_after_logout(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->post('/logout');
        $this->assertGuest();

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_rate_limiting_blocks_repeated_failed_login_attempts(): void
    {
        RateLimiter::clear('admin|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'login' => 'admin',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->from('/login')->post('/login', [
            'login' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertStringContainsString(
            'Too many',
            session('errors')->get('login')[0]
        );
    }

    public function test_login_form_includes_csrf_protection(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);
        $response->assertSee('csrf-token', false);
    }

    public function test_subscriber_cannot_login_to_web_panel(): void
    {
        $subscriber = User::factory()->subscriber()->create([
            'email' => 'sub@example.com',
            'username' => 'subscriber_test',
            'password' => Hash::make('password'),
        ]);
        $subscriber->assignRole('subscriber');

        $response = $this->from('/login')->post('/login', [
            'login' => 'subscriber_test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }
}
