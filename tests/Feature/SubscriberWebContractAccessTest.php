<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MockSubscriberSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberWebContractAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(MockSubscriberSeeder::class);
    }

    public function test_unlinked_subscriber_cannot_read_or_operate_on_the_demo_contract(): void
    {
        $subscriber = User::factory()->subscriber()->create();
        $subscriber->assignRole('subscriber');

        $this->actingAs($subscriber)
            ->getJson(route('subscriber.dashboard.data'))
            ->assertForbidden();

        $this->actingAs($subscriber)
            ->postJson(route('subscriber.recharge.submit'), ['package_id' => 'PKG-70'])
            ->assertForbidden();
    }

    public function test_subscriber_with_linked_contract_can_still_read_dashboard_data(): void
    {
        $subscriber = User::factory()->subscriber()->create([
            'contract_number' => 'TEST-100001',
        ]);
        $subscriber->assignRole('subscriber');

        $this->actingAs($subscriber)
            ->getJson(route('subscriber.dashboard.data'))
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
