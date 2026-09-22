<?php

namespace Tests\Unit;

use App\Contracts\SubscriberProviderInterface;
use App\DTOs\Subscriber\BorrowingData;
use App\DTOs\Subscriber\PackageChangeResult;
use App\DTOs\Subscriber\RechargeResult;
use App\DTOs\Subscriber\SubscriberData;
use App\DTOs\Subscriber\SubscriptionData;
use App\DTOs\Subscriber\UsageData;
use App\Exceptions\Subscriber\PackageChangeFailedException;
use App\Exceptions\Subscriber\RechargeFailedException;
use App\Exceptions\Subscriber\SubscriberNotFoundException;
use App\Exceptions\Subscriber\SubscriberProviderUnavailableException;
use App\Exceptions\Subscriber\SubscriberSuspendedException;
use App\Integrations\Subscriber\Mock\MockSubscriberProvider;
use App\Models\MockSubscriber;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MockSubscriberProviderTest extends TestCase
{
    use RefreshDatabase;

    private MockSubscriberProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = app(MockSubscriberProvider::class);
    }

    public function test_service_container_resolves_mock_subscriber_provider_by_default(): void
    {
        config(['subscriber.provider' => 'mock']);

        $resolved = app(SubscriberProviderInterface::class);

        $this->assertInstanceOf(MockSubscriberProvider::class, $resolved);
    }

    public function test_find_subscriber_by_contract_returns_valid_data(): void
    {
        $subscriber = MockSubscriber::create([
            'contract_number' => 'TEST-001',
            'name' => 'أحمد المهدي',
            'phone' => '0912345678',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-100',
            'current_package_name' => 'ميقا 100G',
            'current_package_price' => 100.00,
            'data_allowance_gb' => 100.00,
            'used_data_gb' => 25.00,
            'expires_at' => now()->addDays(20),
        ]);

        $result = $this->provider->findSubscriberByContract('TEST-001');

        $this->assertInstanceOf(SubscriberData::class, $result);
        $this->assertEquals('TEST-001', $result->contractNumber);
        $this->assertEquals('أحمد المهدي', $result->name);
        $this->assertEquals('0912345678', $result->phone);
        $this->assertEquals('ACTIVE', $result->status);
    }

    public function test_find_subscriber_throws_exception_when_not_found(): void
    {
        $this->expectException(SubscriberNotFoundException::class);

        $this->provider->findSubscriberByContract('NON-EXISTENT');
    }

    public function test_get_subscription_returns_correct_details(): void
    {
        $expiry = Carbon::now()->addDays(15);
        MockSubscriber::create([
            'contract_number' => 'TEST-SUB-01',
            'name' => 'سارة علي',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-150',
            'current_package_name' => 'ميقا 150G',
            'current_package_price' => 150.00,
            'data_allowance_gb' => 150.00,
            'used_data_gb' => 50.00,
            'expires_at' => $expiry,
        ]);

        $sub = $this->provider->getSubscription('TEST-SUB-01');

        $this->assertInstanceOf(SubscriptionData::class, $sub);
        $this->assertEquals('PKG-150', $sub->packageId);
        $this->assertEquals('ميقا 150G', $sub->packageName);
        $this->assertEquals(150.00, $sub->packagePrice);
        $this->assertEquals('150 GB', $sub->dataAllowance);
        $this->assertEquals('ACTIVE', $sub->status);
        $this->assertEquals($expiry->toIso8601String(), $sub->expiresAt);
    }

    public function test_get_usage_calculates_remaining_and_percentage_accurately(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-USAGE-01',
            'name' => 'محمد خالد',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-100',
            'current_package_name' => 'ميقا 100G',
            'current_package_price' => 100.00,
            'data_allowance_gb' => 100.00,
            'used_data_gb' => 40.00,
        ]);

        $usage = $this->provider->getUsage('TEST-USAGE-01');

        $this->assertInstanceOf(UsageData::class, $usage);
        $this->assertEquals(100.00, $usage->totalAllowanceGb);
        $this->assertEquals(40.00, $usage->usedDataGb);
        $this->assertEquals(60.00, $usage->remainingDataGb);
        $this->assertEquals(40.0, $usage->usagePercentage);
    }

    public function test_get_usage_handles_exhausted_quota_without_negative_remaining(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-USAGE-02',
            'name' => 'محمود علي',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
            'current_package_name' => 'ميقا 70G',
            'current_package_price' => 70.00,
            'data_allowance_gb' => 79.00,
            'used_data_gb' => 85.00, // over quota
        ]);

        $usage = $this->provider->getUsage('TEST-USAGE-02');

        $this->assertEquals(0.00, $usage->remainingDataGb);
        $this->assertEquals(107.6, $usage->usagePercentage);
    }

    public function test_get_borrowing_status_returns_valid_data(): void
    {
        $borrowingExpiry = Carbon::now()->addDays(2);
        MockSubscriber::create([
            'contract_number' => 'TEST-BORROW-01',
            'name' => 'كمال حسين',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
            'borrowing_status' => 'ACTIVE',
            'borrowing_used_gb' => 35.00,
            'borrowing_expires_at' => $borrowingExpiry,
        ]);

        $borrowing = $this->provider->getBorrowingStatus('TEST-BORROW-01');

        $this->assertInstanceOf(BorrowingData::class, $borrowing);
        $this->assertEquals('ACTIVE', $borrowing->status);
        $this->assertEquals(70.0, $borrowing->borrowingLimitGb);
        $this->assertEquals(35.0, $borrowing->usedGb);
        $this->assertEquals($borrowingExpiry->toIso8601String(), $borrowing->expiresAt);
    }

    public function test_recharge_successfully_updates_subscriber_record(): void
    {
        $subscriber = MockSubscriber::create([
            'contract_number' => 'TEST-RECHARGE-01',
            'name' => 'طارق الزوي',
            'status' => 'EXPIRED',
            'current_package_id' => 'PKG-70',
            'current_package_name' => 'ميقا 70G',
            'current_package_price' => 70.00,
            'data_allowance_gb' => 79.00,
            'used_data_gb' => 79.00,
            'expires_at' => now()->subDay(),
            'borrowing_status' => 'ACTIVE',
            'borrowing_used_gb' => 20.00,
            'borrowing_expires_at' => now()->addDay(),
        ]);

        $result = $this->provider->recharge('TEST-RECHARGE-01', 'PKG-150');

        $this->assertInstanceOf(RechargeResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->transactionId);

        $subscriber->refresh();
        $this->assertEquals('ACTIVE', $subscriber->status);
        $this->assertEquals('PKG-150', $subscriber->current_package_id);
        $this->assertEquals('ميقا 150G', $subscriber->current_package_name);
        $this->assertEquals(150.00, (float) $subscriber->current_package_price);
        $this->assertEquals(150.00, (float) $subscriber->data_allowance_gb);
        $this->assertEquals(0.00, (float) $subscriber->used_data_gb);
        $this->assertEquals('NOT_ELIGIBLE', $subscriber->borrowing_status);
        $this->assertEquals(0.00, (float) $subscriber->borrowing_used_gb);
        $this->assertNull($subscriber->borrowing_expires_at);
        $this->assertTrue($subscriber->expires_at->isFuture());
    }

    public function test_recharge_throws_exception_for_suspended_subscriber(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-RECHARGE-SUSP',
            'name' => 'موقوف',
            'status' => 'SUSPENDED',
            'current_package_id' => 'PKG-70',
        ]);

        $this->expectException(SubscriberSuspendedException::class);

        $this->provider->recharge('TEST-RECHARGE-SUSP', 'PKG-100');
    }

    public function test_recharge_throws_exception_for_invalid_package(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-RECHARGE-INV',
            'name' => 'نشط',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
        ]);

        $this->expectException(RechargeFailedException::class);

        $this->provider->recharge('TEST-RECHARGE-INV', 'PKG-NONEXISTENT');
    }

    public function test_recharge_is_idempotent_when_idempotency_key_provided(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-RECHARGE-IDEMP',
            'name' => 'مشترك إديمبوتنت',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
            'data_allowance_gb' => 79.00,
            'used_data_gb' => 30.00,
        ]);

        $idempotencyKey = 'IDEMP-RECHARGE-KEY-12345';

        $firstResult = $this->provider->recharge('TEST-RECHARGE-IDEMP', 'PKG-100', $idempotencyKey);
        $secondResult = $this->provider->recharge('TEST-RECHARGE-IDEMP', 'PKG-100', $idempotencyKey);

        $this->assertEquals($firstResult->transactionId, $secondResult->transactionId);
        $this->assertEquals($firstResult->newExpiresAt, $secondResult->newExpiresAt);
        $this->assertEquals($firstResult->packageId, $secondResult->packageId);
    }

    public function test_change_package_successfully_updates_package_and_quota(): void
    {
        $subscriber = MockSubscriber::create([
            'contract_number' => 'TEST-PKG-CHANGE-01',
            'name' => 'مشترك ترقية',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
            'current_package_name' => 'ميقا 70G',
            'current_package_price' => 70.00,
            'data_allowance_gb' => 79.00,
            'used_data_gb' => 50.00,
        ]);

        $result = $this->provider->changePackage('TEST-PKG-CHANGE-01', 'PKG-300');

        $this->assertInstanceOf(PackageChangeResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals('PKG-70', $result->oldPackageId);
        $this->assertEquals('PKG-300', $result->newPackageId);

        $subscriber->refresh();
        $this->assertEquals('PKG-300', $subscriber->current_package_id);
        $this->assertEquals('ميقا 300G', $subscriber->current_package_name);
        $this->assertEquals(300.00, (float) $subscriber->current_package_price);
        $this->assertEquals(300.00, (float) $subscriber->data_allowance_gb);
        $this->assertEquals(0.00, (float) $subscriber->used_data_gb);
    }

    public function test_change_package_throws_exception_when_selecting_same_package(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-PKG-SAME',
            'name' => 'نفس الباقة',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-100',
            'current_package_name' => 'ميقا 100G',
        ]);

        $this->expectException(PackageChangeFailedException::class);

        $this->provider->changePackage('TEST-PKG-SAME', 'PKG-100');
    }

    public function test_change_package_throws_exception_for_suspended_subscriber(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-PKG-SUSP',
            'name' => 'موقوف',
            'status' => 'SUSPENDED',
            'current_package_id' => 'PKG-70',
        ]);

        $this->expectException(SubscriberSuspendedException::class);

        $this->provider->changePackage('TEST-PKG-SUSP', 'PKG-150');
    }

    public function test_change_package_is_idempotent_when_idempotency_key_provided(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-PKG-IDEMP',
            'name' => 'ترقية إديمبوتنت',
            'status' => 'ACTIVE',
            'current_package_id' => 'PKG-70',
            'current_package_name' => 'ميقا 70G',
        ]);

        $idempotencyKey = 'IDEMP-PKG-KEY-99999';

        $firstResult = $this->provider->changePackage('TEST-PKG-IDEMP', 'PKG-150', $idempotencyKey);
        $secondResult = $this->provider->changePackage('TEST-PKG-IDEMP', 'PKG-150', $idempotencyKey);

        $this->assertEquals($firstResult->oldPackageId, $secondResult->oldPackageId);
        $this->assertEquals($firstResult->newPackageId, $secondResult->newPackageId);
        $this->assertEquals($firstResult->newPackageName, $secondResult->newPackageName);
    }

    public function test_simulated_timeout_throws_unavailable_exception(): void
    {
        $this->expectException(SubscriberProviderUnavailableException::class);
        $this->expectExceptionMessage('timed out');

        $this->provider->findSubscriberByContract('SIM-TIMEOUT');
    }

    public function test_simulated_server_error_throws_unavailable_exception(): void
    {
        $this->expectException(SubscriberProviderUnavailableException::class);
        $this->expectExceptionMessage('server error');

        $this->provider->findSubscriberByContract('SIM-500');
    }

    public function test_search_subscribers_matches_name_phone_and_contract(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-100001',
            'name' => 'خالد محمد عبد الله',
            'phone' => '0912345678',
            'status' => 'ACTIVE',
        ]);

        $byContract = $this->provider->searchSubscribers('TEST-100001');
        $byName = $this->provider->searchSubscribers('خالد');
        $byPhone = $this->provider->searchSubscribers('0912345678');

        $this->assertCount(1, $byContract);
        $this->assertCount(1, $byName);
        $this->assertCount(1, $byPhone);
        $this->assertEquals('TEST-100001', $byName->first()->contractNumber);
    }

    public function test_activate_emergency_borrowing_for_eligible_subscriber(): void
    {
        MockSubscriber::create([
            'contract_number' => 'TEST-BORROW-01',
            'name' => 'مؤهل للسلفة',
            'status' => 'ACTIVE',
            'data_allowance_gb' => 100,
            'used_data_gb' => 96,
            'borrowing_status' => 'AVAILABLE',
        ]);

        $result = $this->provider->activateEmergencyBorrowing('TEST-BORROW-01');

        $this->assertInstanceOf(BorrowingData::class, $result);
        $this->assertEquals('ACTIVE', $result->status);

        $this->assertDatabaseHas('mock_subscribers', [
            'contract_number' => 'TEST-BORROW-01',
            'borrowing_status' => 'ACTIVE',
        ]);
    }

    public function test_provision_subscriber_assigns_contract_and_package(): void
    {
        $result = $this->provider->provisionSubscriber('مشترك جديد', '0910001111', 'PKG-70');

        $this->assertEquals('مشترك جديد', $result->name);
        $this->assertStringStartsWith('MEGA-', $result->contractNumber);
        $this->assertDatabaseHas('mock_subscribers', [
            'phone' => '0910001111',
            'current_package_id' => 'PKG-70',
            'status' => 'ACTIVE',
        ]);
    }
}
