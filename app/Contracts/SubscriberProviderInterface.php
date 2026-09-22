<?php

namespace App\Contracts;

use App\DTOs\Subscriber\BorrowingData;
use App\DTOs\Subscriber\PackageChangeResult;
use App\DTOs\Subscriber\RechargeResult;
use App\DTOs\Subscriber\SubscriberData;
use App\DTOs\Subscriber\SubscriptionData;
use App\DTOs\Subscriber\UsageData;
use Illuminate\Support\Collection;

interface SubscriberProviderInterface
{
    public function findSubscriberByContract(string $contractNumber): SubscriberData;

    /**
     * Search subscribers by contract number, name, or phone.
     *
     * @return Collection<int, SubscriberData>
     */
    public function searchSubscribers(string $query): Collection;

    /**
     * Catalog of packages available for recharge / package change.
     *
     * @return array<string, array{name: string, price: float, data_gb: float, speed: string}>
     */
    public function listAvailablePackages(): array;

    public function getSubscription(string $contractNumber): SubscriptionData;

    public function getUsage(string $contractNumber): UsageData;

    public function getBorrowingStatus(string $contractNumber): BorrowingData;

    public function recharge(string $contractNumber, string $packageId, ?string $idempotencyKey = null): RechargeResult;

    public function changePackage(string $contractNumber, string $packageId, ?string $idempotencyKey = null): PackageChangeResult;

    public function activateEmergencyBorrowing(string $contractNumber): BorrowingData;

    public function provisionSubscriber(
        string $name,
        string $phone,
        string $packageId,
        ?string $contractNumber = null,
    ): SubscriberData;
}
