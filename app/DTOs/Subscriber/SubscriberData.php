<?php

namespace App\DTOs\Subscriber;

readonly class SubscriberData
{
    public function __construct(
        public string $subscriberId,
        public string $contractNumber,
        public string $name,
        public ?string $phone,
        public string $status,
    ) {}

    public function toArray(): array
    {
        return [
            'subscriber_id' => $this->subscriberId,
            'contract_number' => $this->contractNumber,
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status,
        ];
    }
}
