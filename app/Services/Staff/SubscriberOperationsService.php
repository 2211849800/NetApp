<?php

namespace App\Services\Staff;

use App\Contracts\SubscriberProviderInterface;
use App\DTOs\Subscriber\BorrowingData;
use App\DTOs\Subscriber\PackageChangeResult;
use App\DTOs\Subscriber\RechargeResult;
use App\Exceptions\Subscriber\BorrowingFailedException;
use App\Exceptions\Subscriber\PackageChangeFailedException;
use App\Exceptions\Subscriber\RechargeFailedException;
use App\Exceptions\Subscriber\SubscriberNotFoundException;
use App\Exceptions\Subscriber\SubscriberProviderUnavailableException;
use App\Exceptions\Subscriber\SubscriberSuspendedException;
use App\Models\Payment;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Str;

class SubscriberOperationsService
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @return array{
     *     subscriber: \App\DTOs\Subscriber\SubscriberData,
     *     subscription: \App\DTOs\Subscriber\SubscriptionData,
     *     usage: \App\DTOs\Subscriber\UsageData,
     *     borrowing: BorrowingData
     * }
     */
    public function profile(string $contractNumber): array
    {
        return [
            'subscriber' => $this->subscriberProvider->findSubscriberByContract($contractNumber),
            'subscription' => $this->subscriberProvider->getSubscription($contractNumber),
            'usage' => $this->subscriberProvider->getUsage($contractNumber),
            'borrowing' => $this->subscriberProvider->getBorrowingStatus($contractNumber),
        ];
    }

    public function recharge(string $contractNumber, string $packageId, ?User $actor = null): RechargeResult
    {
        $result = $this->subscriberProvider->recharge(
            $contractNumber,
            $packageId,
            'staff-recharge-' . Str::uuid(),
        );

        $packages = $this->subscriberProvider->listAvailablePackages();
        $package = $packages[$packageId] ?? null;
        $profile = $this->subscriberProvider->findSubscriberByContract($contractNumber);

        $payment = Payment::create([
            'contract_number' => $contractNumber,
            'subscriber_name' => $profile->name,
            'gateway' => 'cash',
            'amount' => $package['price'] ?? 0,
            'status' => 'completed',
            'reference' => $result->transactionId ?: ('PAY-' . Str::upper(Str::random(10))),
            'notes' => 'شحن باقة من بوابة الموظف',
            'processed_by' => ($actor ?? auth()->user())?->id,
            'paid_at' => now(),
        ]);

        $this->auditLog->log('SUBSCRIBER_RECHARGED', 'Payment', $payment->id, null, [
            'contract_number' => $contractNumber,
            'package_id' => $packageId,
            'transaction_id' => $result->transactionId,
        ], actor: $actor);

        return $result;
    }

    public function changePackage(string $contractNumber, string $packageId, ?User $actor = null): PackageChangeResult
    {
        $old = $this->subscriberProvider->getSubscription($contractNumber);
        $result = $this->subscriberProvider->changePackage(
            $contractNumber,
            $packageId,
            'staff-pkg-' . Str::uuid(),
        );

        $this->auditLog->log('SUBSCRIBER_PACKAGE_CHANGED', 'Subscriber', null, [
            'contract_number' => $contractNumber,
            'old_package_id' => $old->packageId,
        ], [
            'contract_number' => $contractNumber,
            'new_package_id' => $result->newPackageId,
            'new_package_name' => $result->newPackageName,
        ], actor: $actor);

        return $result;
    }

    public function activateEmergencyBorrowing(string $contractNumber, ?User $actor = null): BorrowingData
    {
        $old = $this->subscriberProvider->getBorrowingStatus($contractNumber);
        $result = $this->subscriberProvider->activateEmergencyBorrowing($contractNumber);

        $this->auditLog->log('EMERGENCY_BORROWING_ACTIVATED', 'Subscriber', null, [
            'contract_number' => $contractNumber,
            'status' => $old->status,
        ], [
            'contract_number' => $contractNumber,
            'status' => $result->status,
            'expires_at' => $result->expiresAt,
        ], actor: $actor);

        return $result;
    }

    public function actionErrorMessage(\Throwable $e): string
    {
        return match (true) {
            $e instanceof SubscriberNotFoundException,
            $e instanceof SubscriberSuspendedException,
            $e instanceof RechargeFailedException,
            $e instanceof PackageChangeFailedException,
            $e instanceof BorrowingFailedException,
            $e instanceof SubscriberProviderUnavailableException => $e->getMessage(),
            default => 'تعذر إتمام العملية. حاول مرة أخرى.',
        };
    }
}
