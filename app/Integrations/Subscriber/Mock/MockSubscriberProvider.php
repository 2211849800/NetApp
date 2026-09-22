<?php

namespace App\Integrations\Subscriber\Mock;

use App\Contracts\SubscriberProviderInterface;
use App\DTOs\Subscriber\BorrowingData;
use App\DTOs\Subscriber\PackageChangeResult;
use App\DTOs\Subscriber\RechargeResult;
use App\DTOs\Subscriber\SubscriberData;
use App\DTOs\Subscriber\SubscriptionData;
use App\DTOs\Subscriber\UsageData;
use App\Exceptions\Subscriber\BorrowingFailedException;
use App\Exceptions\Subscriber\PackageChangeFailedException;
use App\Exceptions\Subscriber\RechargeFailedException;
use App\Exceptions\Subscriber\SubscriberNotFoundException;
use App\Exceptions\Subscriber\SubscriberProviderUnavailableException;
use App\Exceptions\Subscriber\SubscriberProvisionFailedException;
use App\Exceptions\Subscriber\SubscriberSuspendedException;
use App\Models\MockSubscriber;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Mock implementation of the SubscriberProviderInterface.
 *
 * This provider reads/writes from the local `mock_subscribers` table
 * and simulates the behavior of the real ISP subscriber-management API.
 *
 * When the real API becomes available, this class will be replaced by a
 * real HTTP-based provider — no other code in the application needs to change.
 */
class MockSubscriberProvider implements SubscriberProviderInterface
{
    /**
     * Find a subscriber by their contract number.
     *
     * @throws SubscriberNotFoundException
     * @throws SubscriberProviderUnavailableException
     */
    public function findSubscriberByContract(string $contractNumber): SubscriberData
    {
        $this->checkFailureSimulation($contractNumber);

        $subscriber = MockSubscriber::byContract($contractNumber)->first();

        if (! $subscriber) {
            throw new SubscriberNotFoundException(
                "المشترك برقم العقد [{$contractNumber}] غير موجود في النظام."
            );
        }

        return $this->toSubscriberData($subscriber);
    }

    /**
     * @return Collection<int, SubscriberData>
     */
    public function searchSubscribers(string $query): Collection
    {
        $term = trim($query);

        $records = MockSubscriber::query()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('contract_number', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->orderBy('contract_number')
            ->limit(50)
            ->get();

        return $records->map(fn (MockSubscriber $subscriber) => $this->toSubscriberData($subscriber));
    }

    public function listAvailablePackages(): array
    {
        return $this->getAvailablePackages();
    }

    /**
     * Get the current subscription details for a subscriber.
     *
     * @throws SubscriberNotFoundException
     * @throws SubscriberProviderUnavailableException
     */
    public function getSubscription(string $contractNumber): SubscriptionData
    {
        $subscriber = $this->findOrFail($contractNumber);

        return new SubscriptionData(
            contractNumber: $subscriber->contract_number,
            packageId: $subscriber->current_package_id,
            packageName: $subscriber->current_package_name,
            packagePrice: (float) $subscriber->current_package_price,
            dataAllowance: ((float) $subscriber->data_allowance_gb) . ' GB',
            status: $subscriber->status,
            expiresAt: $subscriber->expires_at?->toIso8601String(),
        );
    }

    /**
     * Get the current data usage for a subscriber.
     *
     * @throws SubscriberNotFoundException
     * @throws SubscriberProviderUnavailableException
     */
    public function getUsage(string $contractNumber): UsageData
    {
        $subscriber = $this->findOrFail($contractNumber);

        return new UsageData(
            contractNumber: $subscriber->contract_number,
            totalAllowanceGb: (float) $subscriber->data_allowance_gb,
            usedDataGb: (float) $subscriber->used_data_gb,
            remainingDataGb: $subscriber->getRemainingDataGb(),
            usagePercentage: $subscriber->getUsagePercentage(),
        );
    }

    /**
     * Get borrowing/grace status for a subscriber.
     *
     * @throws SubscriberNotFoundException
     * @throws SubscriberProviderUnavailableException
     */
    public function getBorrowingStatus(string $contractNumber): BorrowingData
    {
        $subscriber = $this->findOrFail($contractNumber);

        return new BorrowingData(
            contractNumber: $subscriber->contract_number,
            status: $subscriber->borrowing_status,
            borrowingLimitGb: 70.0,
            usedGb: (float) $subscriber->borrowing_used_gb,
            expiresAt: $subscriber->borrowing_expires_at?->toIso8601String(),
        );
    }

    /**
     * Recharge a subscriber with a specific package.
     *
     * @throws SubscriberNotFoundException
     * @throws SubscriberSuspendedException
     * @throws RechargeFailedException
     * @throws SubscriberProviderUnavailableException
     */
    public function recharge(string $contractNumber, string $packageId, ?string $idempotencyKey = null): RechargeResult
    {
        $this->checkFailureSimulation($contractNumber);

        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $cached = Cache::get("mock_recharge_idemp:{$contractNumber}:{$idempotencyKey}");
            if ($cached instanceof RechargeResult) {
                return $cached;
            }
        }

        $subscriber = $this->findOrFail($contractNumber);

        if ($subscriber->isSuspended()) {
            throw new SubscriberSuspendedException(
                "المشترك برقم العقد [{$contractNumber}] موقوف ولا يمكن شحن حسابه."
            );
        }

        $packageMap = $this->getAvailablePackages();

        if (! isset($packageMap[$packageId])) {
            throw new RechargeFailedException(
                "الباقة [{$packageId}] غير موجودة أو غير متاحة."
            );
        }

        $package = $packageMap[$packageId];
        $newExpiresAt = now()->addDays(30);

        $subscriber->update([
            'current_package_id' => $packageId,
            'current_package_name' => $package['name'],
            'current_package_price' => $package['price'],
            'data_allowance_gb' => $package['data_gb'],
            'used_data_gb' => 0.00,
            'status' => 'ACTIVE',
            'expires_at' => $newExpiresAt,
            // Reset borrowing on recharge
            'borrowing_status' => 'NOT_ELIGIBLE',
            'borrowing_used_gb' => 0.00,
            'borrowing_expires_at' => null,
        ]);

        $result = new RechargeResult(
            success: true,
            contractNumber: $contractNumber,
            packageId: $packageId,
            newExpiresAt: $newExpiresAt->toIso8601String(),
            transactionId: 'MOCK-TXN-' . Str::upper(Str::random(8)),
            message: "تم شحن المشترك بنجاح بباقة {$package['name']}.",
        );

        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            Cache::put("mock_recharge_idemp:{$contractNumber}:{$idempotencyKey}", $result, now()->addHours(24));
        }

        return $result;
    }

    /**
     * Change a subscriber's package.
     *
     * @throws SubscriberNotFoundException
     * @throws SubscriberSuspendedException
     * @throws PackageChangeFailedException
     * @throws SubscriberProviderUnavailableException
     */
    public function changePackage(string $contractNumber, string $packageId, ?string $idempotencyKey = null): PackageChangeResult
    {
        $this->checkFailureSimulation($contractNumber);

        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $cached = Cache::get("mock_pkg_change_idemp:{$contractNumber}:{$idempotencyKey}");
            if ($cached instanceof PackageChangeResult) {
                return $cached;
            }
        }

        $subscriber = $this->findOrFail($contractNumber);

        if ($subscriber->isSuspended()) {
            throw new SubscriberSuspendedException(
                "المشترك برقم العقد [{$contractNumber}] موقوف ولا يمكن تغيير باقته."
            );
        }

        $packageMap = $this->getAvailablePackages();

        if (! isset($packageMap[$packageId])) {
            throw new PackageChangeFailedException(
                "الباقة [{$packageId}] غير موجودة أو غير متاحة."
            );
        }

        $package = $packageMap[$packageId];
        $oldPackageId = $subscriber->current_package_id;

        if ($oldPackageId === $packageId) {
            throw new PackageChangeFailedException(
                "المشترك مشترك بالفعل في هذه الباقة [{$package['name']}]."
            );
        }

        $subscriber->update([
            'current_package_id' => $packageId,
            'current_package_name' => $package['name'],
            'current_package_price' => $package['price'],
            'data_allowance_gb' => $package['data_gb'],
            'used_data_gb' => 0.00,
            'expires_at' => now()->addDays(30),
        ]);

        $result = new PackageChangeResult(
            success: true,
            contractNumber: $contractNumber,
            oldPackageId: $oldPackageId,
            newPackageId: $packageId,
            newPackageName: $package['name'],
            message: "تم تغيير الباقة بنجاح إلى {$package['name']}.",
        );

        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            Cache::put("mock_pkg_change_idemp:{$contractNumber}:{$idempotencyKey}", $result, now()->addHours(24));
        }

        return $result;
    }

    public function activateEmergencyBorrowing(string $contractNumber): BorrowingData
    {
        $this->checkFailureSimulation($contractNumber);

        $subscriber = $this->findOrFail($contractNumber);

        if ($subscriber->isSuspended()) {
            throw new SubscriberSuspendedException(
                "المشترك برقم العقد [{$contractNumber}] موقوف ولا يمكن تفعيل سلفة طوارئ."
            );
        }

        if ($subscriber->borrowing_status === 'ACTIVE') {
            throw BorrowingFailedException::withReason($contractNumber, 'سلفة الطوارئ مفعّلة مسبقاً.');
        }

        if (! in_array($subscriber->borrowing_status, ['AVAILABLE', 'EXPIRED'], true)
            && ! $subscriber->isExpired()
            && $subscriber->getRemainingDataGb() > ($subscriber->data_allowance_gb * 0.1)
        ) {
            throw BorrowingFailedException::withReason(
                $contractNumber,
                'المشترك غير مؤهل لسلفة الطوارئ (الكوتا المتبقية كافية أو الحالة غير مناسبة).'
            );
        }

        $expiresAt = now()->addDays(3);

        $subscriber->update([
            'borrowing_status' => 'ACTIVE',
            'borrowing_used_gb' => 0.00,
            'borrowing_expires_at' => $expiresAt,
            'status' => $subscriber->isExpired() ? 'ACTIVE' : $subscriber->status,
        ]);

        return $this->getBorrowingStatus($contractNumber);
    }

    public function provisionSubscriber(
        string $name,
        string $phone,
        string $packageId,
        ?string $contractNumber = null,
    ): SubscriberData {
        $packageMap = $this->getAvailablePackages();

        if (! isset($packageMap[$packageId])) {
            throw new SubscriberProvisionFailedException(
                "الباقة [{$packageId}] غير موجودة أو غير متاحة."
            );
        }

        $package = $packageMap[$packageId];
        $assignedContract = $contractNumber ?: $this->nextContractNumber();

        if (MockSubscriber::byContract($assignedContract)->exists()) {
            throw new SubscriberProvisionFailedException(
                "رقم العقد [{$assignedContract}] مستخدم مسبقاً."
            );
        }

        $subscriber = MockSubscriber::create([
            'contract_number' => $assignedContract,
            'name' => $name,
            'phone' => $phone,
            'status' => 'ACTIVE',
            'current_package_id' => $packageId,
            'current_package_name' => $package['name'],
            'current_package_price' => $package['price'],
            'data_allowance_gb' => $package['data_gb'],
            'used_data_gb' => 0.00,
            'expires_at' => now()->addDays(30),
            'borrowing_status' => 'NOT_ELIGIBLE',
            'borrowing_used_gb' => 0.00,
            'borrowing_expires_at' => null,
        ]);

        return $this->toSubscriberData($subscriber);
    }

    // ──────────────────────────────────────────────
    // Internal Helpers
    // ──────────────────────────────────────────────

    /**
     * Check for failure simulation triggers.
     *
     * @throws SubscriberProviderUnavailableException
     */
    private function checkFailureSimulation(string $contractNumber): void
    {
        $contractUpper = strtoupper(trim($contractNumber));

        if (str_contains($contractUpper, 'TIMEOUT') || str_contains($contractUpper, 'SIM-TIMEOUT') || str_contains($contractUpper, 'ERR-TIMEOUT')) {
            throw SubscriberProviderUnavailableException::timeout('Mock');
        }

        if (str_contains($contractUpper, '500') || str_contains($contractUpper, 'SIM-500') || str_contains($contractUpper, 'ERR-500') || str_contains($contractUpper, 'SERVER-ERROR')) {
            throw SubscriberProviderUnavailableException::serverError('Mock', 'Simulated 500 Internal Server Error');
        }
    }

    /**
     * Find a mock subscriber or throw.
     *
     * @throws SubscriberNotFoundException
     * @throws SubscriberProviderUnavailableException
     */
    private function findOrFail(string $contractNumber): MockSubscriber
    {
        $this->checkFailureSimulation($contractNumber);

        $subscriber = MockSubscriber::byContract($contractNumber)->first();

        if (! $subscriber) {
            throw new SubscriberNotFoundException(
                "المشترك برقم العقد [{$contractNumber}] غير موجود في النظام."
            );
        }

        return $subscriber;
    }

    private function toSubscriberData(MockSubscriber $subscriber): SubscriberData
    {
        return new SubscriberData(
            subscriberId: (string) $subscriber->id,
            contractNumber: $subscriber->contract_number,
            name: $subscriber->name,
            phone: $subscriber->phone,
            status: $subscriber->status,
        );
    }

    private function nextContractNumber(): string
    {
        $max = MockSubscriber::query()
            ->pluck('contract_number')
            ->map(function (string $value) {
                return preg_match('/(\d+)$/', $value, $matches) ? (int) $matches[1] : 0;
            })
            ->max() ?? 0;

        $next = max(((int) $max) + 1, 200001);

        return 'MEGA-' . $next;
    }

    /**
     * Available mock packages — simulates the real API's package catalog.
     */
    public function getAvailablePackages(): array
    {
        return [
            'PKG-70' => [
                'name' => 'ميقا 70G',
                'price' => 70.00,
                'data_gb' => 79.0,
                'speed' => '70 Mbps',
            ],
            'PKG-100' => [
                'name' => 'ميقا 100G',
                'price' => 100.00,
                'data_gb' => 100.0,
                'speed' => '100 Mbps',
            ],
            'PKG-150' => [
                'name' => 'ميقا 150G',
                'price' => 150.00,
                'data_gb' => 150.0,
                'speed' => '150 Mbps',
            ],
            'PKG-300' => [
                'name' => 'ميقا 300G',
                'price' => 300.00,
                'data_gb' => 300.0,
                'speed' => '300 Mbps',
            ],
            'PKG-500' => [
                'name' => 'ميقا 500G',
                'price' => 500.00,
                'data_gb' => 500.0,
                'speed' => '500 Mbps',
            ],
        ];
    }
}

