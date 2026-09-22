<?php

namespace Tests\Feature;

use App\Models\MockSubscriber;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaffUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriberApiTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(StaffUserSeeder::class);

        $this->adminUser = User::where('email', 'admin@netapp.com')->first();
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/subscribers/100001');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_lookup_subscriber(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100001',
            'name' => 'خالد محمد عبد الله',
            'phone' => '0912345678',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
            'current_package_name' => 'ميقا 70G',
            'current_package_price' => 70.00,
            'data_allowance_gb' => 79.00,
            'used_data_gb' => 35.50,
        ]);

        $response = $this->getJson('/api/v1/subscribers/100001');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'contract_number' => '100001',
                    'name' => 'خالد محمد عبد الله',
                    'status' => 'ACTIVE',
                ],
            ]);
    }

    public function test_subscriber_lookup_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->getJson('/api/v1/subscribers/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_get_subscription_endpoint_returns_data(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100002',
            'name' => 'سالم مفتاح علي',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-150',
            'current_package_name' => 'ميقا 150G',
            'current_package_price' => 150.00,
            'data_allowance_gb' => 150.00,
            'used_data_gb' => 80.00,
        ]);

        $response = $this->getJson('/api/v1/subscribers/100002/subscription');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'contract_number' => '100002',
                    'package_id' => 'PKG-150',
                    'package_name' => 'ميقا 150G',
                    'package_price' => 150.00,
                ],
            ]);
    }

    public function test_get_usage_endpoint_returns_data(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100004',
            'name' => 'عمر عبد اللطيف',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-300',
            'current_package_name' => 'ميقا 300G',
            'current_package_price' => 300.00,
            'data_allowance_gb' => 300.00,
            'used_data_gb' => 45.00,
        ]);

        $response = $this->getJson('/api/v1/subscribers/100004/usage');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'contract_number' => '100004',
                    'total_allowance_gb' => 300.0,
                    'used_data_gb' => 45.0,
                    'remaining_data_gb' => 255.0,
                    'usage_percentage' => 15.0,
                ],
            ]);
    }

    public function test_get_borrowing_endpoint_returns_data(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100003',
            'name' => 'الهادي أحمد ربيض',
            'status' => 'EXPIRED',
            'current_package_id' => 'PKG-70',
            'borrowing_status' => 'ACTIVE',
            'borrowing_used_gb' => 22.50,
        ]);

        $response = $this->getJson('/api/v1/subscribers/100003/borrowing');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'contract_number' => '100003',
                    'status' => 'ACTIVE',
                    'used_gb' => 22.5,
                ],
            ]);
    }

    public function test_recharge_endpoint_executes_successfully(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100005',
            'name' => 'طارق الزوي',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
            'current_package_name' => 'ميقا 70G',
            'data_allowance_gb' => 79.00,
            'used_data_gb' => 70.00,
        ]);

        $response = $this->postJson('/api/v1/subscribers/100005/recharge', [
            'package_id' => 'PKG-100',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'success' => true,
                    'contract_number' => '100005',
                    'package_id' => 'PKG-100',
                ],
            ]);
    }

    public function test_recharge_fails_for_suspended_subscriber_with_403(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100006',
            'name' => 'مروان السنوسي',
            'status' => 'SUSPENDED',
            'current_package_id' => 'PKG-150',
        ]);

        $response = $this->postJson('/api/v1/subscribers/100006/recharge', [
            'package_id' => 'PKG-150',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_change_package_fails_for_same_package_with_422(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100007',
            'name' => 'منيرة الفيتوري',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-500',
            'current_package_name' => 'ميقا 500G',
        ]);

        $response = $this->postJson('/api/v1/subscribers/100007/change-package', [
            'package_id' => 'PKG-500',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_provider_timeout_simulation_returns_504(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->getJson('/api/v1/subscribers/SIM-TIMEOUT');

        $response->assertStatus(504)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_provider_server_error_simulation_returns_503(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->getJson('/api/v1/subscribers/SIM-500');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_recharge_idempotency_via_http_header(): void
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        MockSubscriber::create([
            'contract_number' => '100008',
            'name' => 'فاطمة عبد السلام',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
        ]);

        $idempKey = 'API-IDEMP-' . uniqid();

        $resp1 = $this->postJson(
            '/api/v1/subscribers/100008/recharge',
            ['package_id' => 'PKG-100'],
            ['X-Idempotency-Key' => $idempKey]
        );

        $resp1->assertStatus(200);
        $txnId1 = $resp1->json('data.transaction_id');

        $resp2 = $this->postJson(
            '/api/v1/subscribers/100008/recharge',
            ['package_id' => 'PKG-100'],
            ['X-Idempotency-Key' => $idempKey]
        );

        $resp2->assertStatus(200);
        $txnId2 = $resp2->json('data.transaction_id');

        $this->assertEquals($txnId1, $txnId2);
    }
}
