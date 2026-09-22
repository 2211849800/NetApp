<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@netapp.com')->firstOrFail();
    }

    public function test_admin_can_view_employees_page(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.employees.index'));

        $response->assertOk();
        $response->assertSee('إدارة الموظفين');
    }

    public function test_admin_can_create_employee_with_username_and_password(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.employees.store'), [
                'name' => 'أحمد علي',
                'username' => 'ahmed_ali',
                'phone' => '0910000001',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'employee',
            ]);

        $response->assertRedirect(route('admin.employees.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'username' => 'ahmed_ali',
            'name' => 'أحمد علي',
            'phone' => '0910000001',
        ]);

        $employee = User::where('username', 'ahmed_ali')->first();
        $this->assertTrue($employee->hasRole('employee'));

        $this->post('/logout');

        $this->post('/login', [
            'login' => 'ahmed_ali',
            'password' => 'password123',
        ])->assertRedirect(route('employee.dashboard'));
    }

    public function test_employee_cannot_access_employee_management(): void
    {
        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('admin.employees.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('admin.employees.create'))
            ->assertForbidden();
    }

    public function test_duplicate_username_is_rejected(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('admin.employees.create'))
            ->post(route('admin.employees.store'), [
                'name' => 'Duplicate User',
                'username' => 'employee',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'employee',
            ]);

        $response->assertRedirect(route('admin.employees.create'));
        $response->assertSessionHasErrors('username');
    }
}
