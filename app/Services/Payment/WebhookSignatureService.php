<?php

namespace App\Services\Payment;

class WebhookSignatureService
{
    public function verify(string $gateway, string $payloadContent, ?string $signature): bool
    {
        if (empty($signature)) {
            return false;
        }

        // Allow mock bypass signature during mock testing
        if (config('app.env') !== 'production' && ($signature === 'mock-signature' || $signature === 'test-signature')) {
            return true;
        }

        $expectedSignature = $this->generateSignature($gateway, $payloadContent);

        return hash_equals($expectedSignature, $signature);
    }

    public function generateSignature(string $gateway, string $payloadContent): string
    {
        $secret = match (strtolower($gateway)) {
            'lypay' => config('services.lypay.webhook_secret', env('LYPAY_WEBHOOK_SECRET', 'mock-lypay-secret-123')),
            'onepay' => config('services.onepay.webhook_secret', env('ONEPAY_WEBHOOK_SECRET', 'mock-onepay-secret-123')),
            default => 'mock-default-secret-123',
        };

        return hash_hmac('sha256', $payloadContent, $secret);
    }
}
