<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentWebhookProcessor;
use App\Services\Payment\WebhookSignatureService;
use Illuminate\Console\Command;

class SimulatePaymentWebhookCommand extends Command
{
    protected $signature = 'webhook:simulate
                            {--gateway=lypay : Payment gateway (lypay or onepay)}
                            {--contract=TEST-100001 : Subscriber contract number}
                            {--package=1 : Package ID to recharge}
                            {--amount=70 : Payment amount in LYD}
                            {--reference= : Optional unique transaction reference}';

    protected $description = 'Simulate a payment gateway webhook notification (Lypay / OnePay) and execute subscriber recharge.';

    public function handle(PaymentWebhookProcessor $processor, WebhookSignatureService $signatureService): int
    {
        $gateway = (string) $this->option('gateway');
        $contract = (string) $this->option('contract');
        $package = (string) $this->option('package');
        $amount = (float) $this->option('amount');
        $reference = $this->option('reference') ? (string) $this->option('reference') : strtoupper($gateway).'-SIM-'.rand(100000, 999999);

        $payload = [
            'reference' => $reference,
            'contract_number' => $contract,
            'amount' => $amount,
            'package_id' => $package,
            'status' => 'SUCCESS',
            'timestamp' => now()->toIso8601String(),
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $signature = $signatureService->generateSignature($gateway, $payloadJson);

        $this->info("[محاكي الـ Webhook] جاري إرسال إشعار دفع وهمي لبوابة ({$gateway})...");
        $this->line("رقم العقد: {$contract} | المبالغ: {$amount} د.ل | المعاملة: {$reference}");
        $this->line("التوقيع التشفيري (HMAC-SHA256): {$signature}");

        try {
            $result = $processor->process($gateway, $payload);

            if ($result['already_processed'] ?? false) {
                $this->warn("[معاملة مكررة] المعاملة ({$reference}) معالجة مسبقاً.");
            } else {
                $this->info("[نجحت العملية] {$result['message']}");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("[فشلت العملية] ".$e->getMessage());

            return self::FAILURE;
        }
    }
}
