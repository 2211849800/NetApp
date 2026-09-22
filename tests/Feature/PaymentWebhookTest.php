<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\WebhookSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\MockSubscriberSeeder::class);
    }


    public function test_lypay_webhook_successfully_processes_payment_and_recharges_subscriber(): void
    {
        $payload = [
            'reference' => 'LYP-TEST-9901',
            'contract_number' => 'TEST-100001',
            'amount' => 70.00,
            'package_id' => '1',
            'status' => 'SUCCESS',
        ];

        $payloadJson = json_encode($payload);

        $response = $this->postJson('/api/v1/webhooks/lypay', $payload, [
            'X-Lypay-Signature' => 'mock-signature',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('payments', [
            'reference' => 'LYP-TEST-9901',
            'contract_number' => 'TEST-100001',
            'gateway' => 'lypay',
            'amount' => 70.00,
            'status' => 'completed',
        ]);
    }

    public function test_onepay_webhook_successfully_processes_payment(): void
    {
        $payload = [
            'reference' => 'ONE-TEST-8802',
            'contract_number' => 'TEST-100002',
            'amount' => 150.00,
            'package_id' => '2',
            'status' => 'SUCCESS',
        ];

        $response = $this->postJson('/api/v1/webhooks/onepay', $payload, [
            'X-OnePay-Signature' => 'mock-signature',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('payments', [
            'reference' => 'ONE-TEST-8802',
            'contract_number' => 'TEST-100002',
            'gateway' => 'onepay',
            'amount' => 150.00,
        ]);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $payload = [
            'reference' => 'INVALID-SIG-001',
            'contract_number' => 'TEST-100001',
            'amount' => 70.00,
        ];

        $response = $this->postJson('/api/v1/webhooks/lypay', $payload, [
            'X-Lypay-Signature' => 'wrong-signature-key-12345',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('status', 'error');

        $this->assertDatabaseMissing('payments', [
            'reference' => 'INVALID-SIG-001',
        ]);
    }

    public function test_webhook_idempotency_prevents_duplicate_recharges(): void
    {
        Payment::create([
            'reference' => 'DUP-REF-100',
            'contract_number' => 'TEST-100001',
            'subscriber_name' => 'خالد محمد عبد الله',
            'gateway' => 'lypay',
            'amount' => 70.00,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $payload = [
            'reference' => 'DUP-REF-100',
            'contract_number' => 'TEST-100001',
            'amount' => 70.00,
            'package_id' => '1',
        ];

        $response = $this->postJson('/api/v1/webhooks/lypay', $payload, [
            'X-Lypay-Signature' => 'mock-signature',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.already_processed', true);
    }

    public function test_webhook_artisan_simulation_command(): void
    {
        $this->artisan('webhook:simulate', [
            '--gateway' => 'lypay',
            '--contract' => 'TEST-100001',
            '--amount' => 70.00,
            '--reference' => 'CLI-SIM-999',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('payments', [
            'reference' => 'CLI-SIM-999',
            'contract_number' => 'TEST-100001',
            'amount' => 70.00,
        ]);
    }
}
