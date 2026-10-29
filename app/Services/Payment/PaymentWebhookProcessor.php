<?php

namespace App\Services\Payment;

use App\Contracts\SubscriberProviderInterface;
use App\Models\Payment;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use App\Services\Subscriber\SubscriberNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PaymentWebhookProcessor
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly AuditLogService $auditLog,
        private readonly SubscriberNotificationService $notifications,
    ) {}

    public function process(string $gateway, array $payload): array
    {
        $gateway = strtolower(trim($gateway));
        if (! in_array($gateway, ['lypay', 'onepay'], true)) {
            throw new \InvalidArgumentException('Unsupported payment gateway.');
        }

        $status = strtoupper(trim((string) ($payload['status'] ?? '')));
        if ($status !== 'SUCCESS') {
            throw new \InvalidArgumentException('Payment notification is not successful.');
        }

        $reference = trim((string) ($payload['reference'] ?? $payload['transaction_id'] ?? $payload['payment_id'] ?? ''));
        if ($reference === '' || mb_strlen($reference) > 255) {
            throw new \InvalidArgumentException('A valid payment reference is required.');
        }

        $contractNumber = trim((string) ($payload['contract_number'] ?? $payload['contract'] ?? ''));
        if ($contractNumber === '' || mb_strlen($contractNumber) > 255) {
            throw new \InvalidArgumentException('A valid subscriber contract number is required.');
        }

        $rawAmount = $payload['amount'] ?? null;
        if (! is_numeric($rawAmount) || ! is_finite((float) $rawAmount) || (float) $rawAmount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be a positive number.');
        }
        $amount = number_format((float) $rawAmount, 2, '.', '');

        $packageId = trim((string) ($payload['package_id'] ?? ''));
        $availablePackages = $this->subscriberProvider->listAvailablePackages();
        if (! isset($availablePackages[$packageId])) {
            $packageId = match ($packageId) {
                '1' => 'PKG-70',
                '2' => 'PKG-150',
                '3' => 'PKG-300',
                default => '',
            };
        }

        if ($packageId === '' || ! isset($availablePackages[$packageId])) {
            throw new \InvalidArgumentException('The selected package is not available.');
        }

        $expectedAmount = number_format((float) $availablePackages[$packageId]['price'], 2, '.', '');
        if ($amount !== $expectedAmount) {
            throw new \InvalidArgumentException('Payment amount does not match the selected package price.');
        }

        $subscriberName = trim((string) ($payload['subscriber_name'] ?? ''));
        $user = User::where('contract_number', $contractNumber)->first();
        if ($subscriberName === '' && $user) {
            $subscriberName = $user->name;
        }

        $payment = $this->claimPayment(
            $gateway,
            $reference,
            $contractNumber,
            $packageId,
            $amount,
            $subscriberName !== '' ? $subscriberName : 'Subscriber '.$contractNumber,
            $user?->id,
        );

        if ($payment->status === 'completed') {
            return [
                'success' => true,
                'already_processed' => true,
                'payment' => $payment,
                'message' => 'Payment notification was already processed.',
            ];
        }

        // Reuse the gateway reference so retries cannot recharge twice when the provider supports idempotency.
        $rechargeResult = $this->subscriberProvider->recharge(
            $contractNumber,
            $packageId,
            'webhook-'.$gateway.'-'.$reference,
        );

        if (! $rechargeResult->success) {
            throw new \RuntimeException('Subscriber recharge did not complete.');
        }

        $payment = DB::transaction(function () use ($payment, $gateway, $reference, $contractNumber, $packageId, $amount): Payment {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertSamePayment($lockedPayment, $gateway, $contractNumber, $packageId, $amount);

            if ($lockedPayment->status !== 'completed') {
                $lockedPayment->update([
                    'status' => 'completed',
                    'notes' => "Gateway {$gateway} confirmed payment {$reference}; subscriber recharge completed.",
                    'paid_at' => $lockedPayment->paid_at ?? now(),
                ]);
            }

            return $lockedPayment->fresh();
        });

        $this->auditLog->log('WEBHOOK_PAYMENT_PROCESSED', 'Payment', $payment->id, null, [
            'gateway' => $gateway,
            'reference' => $reference,
            'contract_number' => $contractNumber,
            'package_id' => $packageId,
            'amount' => $amount,
            'recharge_message' => $rechargeResult->message,
        ]);

        $this->notifications->notifyRecharge(
            $contractNumber,
            $availablePackages[$packageId]['name'],
            'payment-'.$payment->id,
        );

        return [
            'success' => true,
            'already_processed' => false,
            'payment' => $payment,
            'recharge_result' => $rechargeResult,
            'message' => 'Payment notification and subscriber recharge processed successfully.',
        ];
    }

    private function claimPayment(
        string $gateway,
        string $reference,
        string $contractNumber,
        string $packageId,
        string $amount,
        string $subscriberName,
        ?int $userId,
    ): Payment {
        try {
            return DB::transaction(function () use ($gateway, $reference, $contractNumber, $packageId, $amount, $subscriberName, $userId): Payment {
                $payment = Payment::query()->where('reference', $reference)->lockForUpdate()->first();

                if (! $payment) {
                    $payment = Payment::query()->create([
                        'reference' => $reference,
                        'contract_number' => $contractNumber,
                        'subscriber_name' => $subscriberName,
                        'user_id' => $userId,
                        'gateway' => $gateway,
                        'package_id' => $packageId,
                        'amount' => $amount,
                        'status' => 'pending',
                        'notes' => "Gateway {$gateway} confirmed payment; subscriber recharge is pending.",
                        'paid_at' => now(),
                    ]);
                }

                $this->assertSamePayment($payment, $gateway, $contractNumber, $packageId, $amount);

                return $payment;
            });
        } catch (QueryException $exception) {
            // A concurrent callback may have inserted the same unique reference first.
            $payment = Payment::query()->where('reference', $reference)->first();
            if (! $payment) {
                throw $exception;
            }

            $this->assertSamePayment($payment, $gateway, $contractNumber, $packageId, $amount);

            return $payment;
        }
    }

    private function assertSamePayment(Payment $payment, string $gateway, string $contractNumber, string $packageId, string $amount): void
    {
        $sameAmount = number_format((float) $payment->amount, 2, '.', '') === $amount;
        $samePackage = $payment->package_id === null || $payment->package_id === $packageId;

        if ($payment->gateway !== $gateway
            || $payment->contract_number !== $contractNumber
            || ! $sameAmount
            || ! $samePackage) {
            throw new \InvalidArgumentException('Payment reference was already used with different payment details.');
        }
    }
}
