<?php

namespace App\DTOs\Subscriber;

readonly class SubscriptionData
{
    public function __construct(
        public string $contractNumber,
        public string $packageId,
        public string $packageName,
        public float $packagePrice,
        public string $dataAllowance,
        public string $status,
        public ?string $expiresAt,
    ) {}

    public function toArray(): array
    {
        return [
            'contract_number' => $this->contractNumber,
            'package_id' => $this->packageId,
            'package_name' => $this->packageName,
            'package_price' => $this->packagePrice,
            'data_allowance' => $this->dataAllowance,
            'status' => $this->status,
            'expires_at' => $this->expiresAt,
        ];
    }
}
