<?php

namespace App\DTOs\Subscriber;

readonly class BorrowingData
{
    public function __construct(
        public string $contractNumber,
        public string $status, // NOT_ELIGIBLE, AVAILABLE, ACTIVE, EXPIRED
        public float $borrowingLimitGb = 70.0,
        public float $usedGb = 0.0,
        public ?string $expiresAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'contract_number' => $this->contractNumber,
            'status' => $this->status,
            'borrowing_limit_gb' => $this->borrowingLimitGb,
            'used_gb' => $this->usedGb,
            'expires_at' => $this->expiresAt,
        ];
    }
}
