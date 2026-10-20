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

        $secret = $this->secretFor($gateway);
        if ($secret === null) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payloadContent, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    public function generateSignature(string $gateway, string $payloadContent): string
    {
        $secret = $this->secretFor($gateway);

        if ($secret === null) {
            throw new \InvalidArgumentException('Webhook secret is not configured for this gateway.');
        }

        return hash_hmac('sha256', $payloadContent, $secret);
    }

    private function secretFor(string $gateway): ?string
    {
        $secret = match (strtolower($gateway)) {
            'lypay' => config('services.lypay.webhook_secret'),
            'onepay' => config('services.onepay.webhook_secret'),
            default => null,
        };

        return is_string($secret) && trim($secret) !== '' ? $secret : null;
    }
}
