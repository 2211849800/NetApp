<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MockSubscriberSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberApiAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);
        $this->seed(MockSubscriberSeeder::class);
    }

    public function test_subscriber_cannot_access_contract_lookup_api(): void
    {
        $subscriber = User::factory()->subscriber()->create([
            'contract_number' => 'TEST-100001',
        ]);

        $this->actingAs($subscriber, 'sanctum')
            ->getJson('/api/v1/subscribers/TEST-100001')
            ->assertForbidden();
    }

    public function test_self_service_uses_the_contract_linked_to_the_account(): void
    {
        $subscriber = User::factory()->subscriber()->create([
            'contract_number' => 'TEST-100001',
        ]);

        $this->actingAs($subscriber, 'sanctum')
            ->getJson('/api/v1/subscriber/profile?contract_number=TEST-100002')
            ->assertOk()
            ->assertJsonPath('data.contract_number', 'TEST-100001');
    }

    public function test_self_service_rejects_accounts_without_a_linked_contract(): void
    {
        $subscriber = User::factory()->subscriber()->create();

        $this->actingAs($subscriber, 'sanctum')
            ->getJson('/api/v1/subscriber/profile?contract_number=TEST-100001')
            ->assertForbidden();
    }

    public function test_staff_with_subscriber_permission_can_look_up_contracts(): void
    {
        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee, 'sanctum')
            ->getJson('/api/v1/subscribers/TEST-100001')
            ->assertOk()
            ->assertJsonPath('data.contract_number', 'TEST-100001');
    }

    public function test_employee_without_subscriber_permission_cannot_look_up_contracts(): void
    {
        $employee = User::factory()->employee()->create();

        $this->actingAs($employee, 'sanctum')
            ->getJson('/api/v1/subscribers/TEST-100001')
            ->assertForbidden();
    }

    public function test_staff_with_manage_subscribers_permission_can_recharge_contract(): void
    {
        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee, 'sanctum')
            ->postJson('/api/v1/subscribers/TEST-100001/recharge', [
                'package_id' => 'PKG-70',
            ])
            ->assertOk()
            ->assertJsonPath('data.contract_number', 'TEST-100001');
    }
}
