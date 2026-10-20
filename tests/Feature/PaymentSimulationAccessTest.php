<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MockSubscriberSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSimulationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);
        $this->seed(MockSubscriberSeeder::class);
    }

    public function test_api_simulation_rejects_unauthenticated_requests(): void
    {
        $this->postJson('/api/v1/webhooks/simulate', $this->simulationPayload())
            ->assertUnauthorized();
    }

    public function test_api_simulation_rejects_subscriber_accounts(): void
    {
        $subscriber = User::factory()->subscriber()->create();

        $this->actingAs($subscriber, 'sanctum')
            ->postJson('/api/v1/webhooks/simulate', $this->simulationPayload())
            ->assertForbidden();
    }

    public function test_api_simulation_remains_available_to_staff_outside_production(): void
    {
        $employee = User::where('email', 'employee@example.com')->firstOrFail();

        $this->actingAs($employee, 'sanctum')
            ->post('/api/v1/webhooks/simulate', $this->simulationPayload())
            ->assertRedirect();
    }

    public function test_payment_page_simulation_remains_available_to_authorized_staff_outside_production(): void
    {
        $admin = User::where('email', 'admin@netapp.com')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('staff.payments.simulate'), $this->simulationPayload())
            ->assertRedirect();
    }

    public function test_simulation_is_disabled_in_production_even_for_staff(): void
    {
        $employee = User::where('email', 'employee@example.com')->firstOrFail();
        $this->app['env'] = 'production';

        $this->actingAs($employee, 'sanctum')
            ->postJson('/api/v1/webhooks/simulate', $this->simulationPayload())
            ->assertNotFound();
    }

    private function simulationPayload(): array
    {
        return [
            'gateway' => 'lypay',
            'contract_number' => 'TEST-100001',
            'amount' => 70,
            'package_id' => '1',
        ];
    }
}
