<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);
    }

    public function test_admin_dashboard_renders_successfully(): void
    {
        $admin = User::where('email', 'admin@netapp.com')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('لوحة تحكم المدير');
        $response->assertSee('admin');
    }

    public function test_subscribers_page_renders_for_authorized_staff(): void
    {
        $employee = User::where('email', 'employee@example.com')->first();

        $response = $this->actingAs($employee)->get(route('staff.subscribers.index'));

        $response->assertStatus(200);
        $response->assertSee('إدارة المشتركين');
    }

    public function test_packages_page_renders_for_admin(): void
    {
        $admin = User::where('email', 'admin@netapp.com')->first();

        $response = $this->actingAs($admin)->get(route('admin.packages.index'));

        $response->assertStatus(200);
        $response->assertSee('باقات الإنترنت');
    }

    public function test_complaints_page_renders_for_employee(): void
    {
        $employee = User::where('email', 'employee@example.com')->first();

        $response = $this->actingAs($employee)->get(route('staff.complaints.index'));

        $response->assertStatus(200);
        $response->assertSee('إدارة الشكاوى والطلبات');
    }

    public function test_payments_page_renders_for_employee(): void
    {
        $employee = User::where('email', 'employee@example.com')->first();

        $response = $this->actingAs($employee)->get(route('staff.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('سجل المدفوعات والمعاملات');
    }

    public function test_mobile_app_demo_page_renders_for_staff(): void
    {
        $admin = User::where('email', 'admin@netapp.com')->first();

        $response = $this->actingAs($admin)->get(route('mobile.demo'));

        $response->assertStatus(200);
        $response->assertSee('تطبيق المشتركين');
    }
}
