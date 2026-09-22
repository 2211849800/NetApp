<?php

namespace App\DTOs\Subscriber;

readonly class RechargeResult
{
    public function __construct(
        public bool $success,
        public string $contractNumber,
        public string $packageId,
        public string $newExpiresAt,
        public ?string $transactionId = null,
        public ?string $message = null,
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'contract_number' => $this->contractNumber,
            'package_id' => $this->packageId,
            'new_expires_at' => $this->newExpiresAt,
            'transaction_id' => $this->transactionId,
            'message' => $this->message,
        ];
    }
}
