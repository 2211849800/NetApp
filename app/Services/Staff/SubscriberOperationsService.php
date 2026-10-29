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
use App\Exceptions\Employee\InsufficientEmployeeBalanceException;
use App\Models\Payment;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Employee\EmployeeWalletService;
use App\Services\Subscriber\SubscriberNotificationService;
use Illuminate\Support\Str;

class SubscriberOperationsService
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly AuditLogService $auditLog,
        private readonly EmployeeWalletService $wallet,
        private readonly SubscriberNotificationService $notifications,
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
        $packages = $this->subscriberProvider->listAvailablePackages();
        $package = $packages[$packageId] ?? null;
        $actor ??= auth()->user();
        $employeeDebit = null;

        if ($actor?->hasRole('employee')) {
            if (! $package) {
                throw new RechargeFailedException('الباقة المحددة غير متاحة.');
            }

            $employeeDebit = $this->wallet->debit(
                $actor,
                (float) $package['price'],
                "شحن عقد {$contractNumber}"
            );
        }

        try {
            $result = $this->subscriberProvider->recharge(
                $contractNumber,
                $packageId,
                'staff-recharge-' . Str::uuid(),
            );
        } catch (\Throwable $exception) {
            if ($employeeDebit && $actor) {
                $this->wallet->refund($actor, (float) $employeeDebit->amount, "إرجاع قيمة شحن العقد {$contractNumber} بعد فشل العملية");
            }

            throw $exception;
        }

        $profile = $this->subscriberProvider->findSubscriberByContract($contractNumber);

        $payment = Payment::create([
            'contract_number' => $contractNumber,
            'subscriber_name' => $profile->name,
            'gateway' => 'cash',
            'package_id' => $packageId,
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

        $this->notifications->notifyRecharge(
            $contractNumber,
            $package['name'] ?? $packageId,
            'payment-'.$payment->id,
            $actor,
        );

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
            $e instanceof InsufficientEmployeeBalanceException,
            $e instanceof SubscriberProviderUnavailableException => $e->getMessage(),
            default => 'تعذر إتمام العملية. حاول مرة أخرى.',
        };
    }
}
