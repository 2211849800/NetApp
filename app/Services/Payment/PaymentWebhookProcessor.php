<?php

namespace App\Services\Payment;

use App\Contracts\SubscriberProviderInterface;
use App\Models\Payment;
use App\Models\User;
use App\Services\Audit\AuditLogService;

class PaymentWebhookProcessor
{
    public function __construct(
        private readonly SubscriberProviderInterface $subscriberProvider,
        private readonly AuditLogService $auditLog,
    ) {}

    public function process(string $gateway, array $payload): array
    {
        $reference = (string) ($payload['reference'] ?? $payload['transaction_id'] ?? $payload['payment_id'] ?? 'PAY-'.strtoupper(uniqid()));
        $contractNumber = (string) ($payload['contract_number'] ?? $payload['contract'] ?? '');
        $amount = (float) ($payload['amount'] ?? 0);
        $packageId = (string) ($payload['package_id'] ?? 'PKG-70');
        $availablePackages = $this->subscriberProvider->listAvailablePackages();
        if (! isset($availablePackages[$packageId])) {
            $packageId = match ($packageId) {
                '2' => 'PKG-150',
                '3' => 'PKG-300',
                default => 'PKG-70',
            };
        }


        $subscriberName = (string) ($payload['subscriber_name'] ?? '');

        if ($contractNumber === '') {
            throw new \InvalidArgumentException('رقم العقد مطلوب لمعالجة إشعار الدفع.');
        }

        // Idempotency check: Return immediately if transaction already processed
        $existing = Payment::where('reference', $reference)->first();

        if ($existing && $existing->status === 'completed') {
            return [
                'success' => true,
                'already_processed' => true,
                'payment' => $existing,
                'message' => 'تمت معالجة إشعار الدفع هذا مسبقاً (معاملة مكررة).',
            ];
        }

        $user = User::where('contract_number', $contractNumber)->first();

        if (! $subscriberName && $user) {
            $subscriberName = $user->name;
        }

        // Save Payment record
        $payment = Payment::updateOrCreate(
            ['reference' => $reference],
            [
                'user_id' => $user?->id,
                'contract_number' => $contractNumber,
                'subscriber_name' => $subscriberName ?: 'مشترك '.$contractNumber,
                'gateway' => strtolower($gateway),
                'amount' => $amount,
                'status' => 'completed',
                'notes' => 'تم استلام الدفعة آلياً عبر إشعار الـ Webhook الخاص بـ '.$gateway,
                'paid_at' => now(),
            ]
        );

        // Perform automated subscriber package recharge
        $rechargeResult = $this->subscriberProvider->recharge($contractNumber, $packageId);

        // Log audit trail
        $this->auditLog->log('WEBHOOK_PAYMENT_PROCESSED', 'Payment', $payment->id, null, [
            'gateway' => $gateway,
            'reference' => $reference,
            'contract_number' => $contractNumber,
            'amount' => $amount,
            'recharge_message' => $rechargeResult->message ?? null,
        ]);

        return [
            'success' => true,
            'already_processed' => false,
            'payment' => $payment,
            'recharge_result' => $rechargeResult,
            'message' => 'تمت معالجة إشعار الدفع بنجاح وتحديث حساب المشترك.',
        ];
    }
}
