<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriberApiV1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\MockSubscriberSeeder::class);
    }

    public function test_subscriber_can_fetch_profile_via_api(): void
    {
        $subscriber = User::create([
            'name' => 'خالد محمد عبد الله',
            'username' => 'TEST-100001',
            'phone' => '0912345678',
            'password' => bcrypt('password123'),
            'type' => 'subscriber',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $subscriber->assignRole('subscriber');

        Sanctum::actingAs($subscriber, ['*']);

        $response = $this->getJson('/api/v1/subscriber/profile');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.contract_number', 'TEST-100001');
    }

    public function test_subscriber_can_fetch_usage_via_api(): void
    {
        $subscriber = User::create([
            'name' => 'خالد محمد عبد الله',
            'username' => 'TEST-100001',
            'phone' => '0912345678',
            'password' => bcrypt('password123'),
            'type' => 'subscriber',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $subscriber->assignRole('subscriber');

        Sanctum::actingAs($subscriber, ['*']);

        $response = $this->getJson('/api/v1/subscriber/usage');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.contract_number', 'TEST-100001');
    }

    public function test_subscriber_can_recharge_package_via_api(): void
    {
        $subscriber = User::create([
            'name' => 'خالد محمد عبد الله',
            'username' => 'TEST-100001',
            'phone' => '0912345678',
            'password' => bcrypt('password123'),
            'type' => 'subscriber',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $subscriber->assignRole('subscriber');

        Sanctum::actingAs($subscriber, ['*']);

        $response = $this->postJson('/api/v1/subscriber/recharge', [
            'package_id' => 'PKG-70',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.contract_number', 'TEST-100001');
    }

    public function test_subscriber_web_portal_renders_successfully(): void
    {
        $subscriber = User::create([
            'name' => 'خالد محمد عبد الله',
            'username' => 'TEST-100001',
            'phone' => '0912345678',
            'password' => bcrypt('password123'),
            'type' => 'subscriber',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $subscriber->assignRole('subscriber');

        $response = $this->actingAs($subscriber)->get('/subscriber/dashboard');

        $response->assertStatus(200);
        $response->assertSee('بوابة المشترك');
        $response->assertSee('TEST-100001');
    }

    public function test_admin_can_update_system_settings(): void
    {
        $admin = User::factory()->create([
            'type' => 'employee',
            'status' => \App\Enums\UserStatus::Active,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'subscriber_provider' => 'mock',
            'emergency_borrowing_gb' => 70,
            'emergency_borrowing_days' => 3,
            'currency_symbol' => 'د.ل',
            'support_phone' => '0910000000',
            'payment_mock_mode' => 'true',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'key' => 'emergency_borrowing_gb',
            'value' => '70',
        ]);
    }
}
