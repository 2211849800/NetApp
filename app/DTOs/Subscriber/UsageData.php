<?php

namespace App\DTOs\Subscriber;

readonly class UsageData
{
    public function __construct(
        public string $contractNumber,
        public float $totalAllowanceGb,
        public float $usedDataGb,
        public float $remainingDataGb,
        public float $usagePercentage,
    ) {}

    public function toArray(): array
    {
        return [
            'contract_number' => $this->contractNumber,
            'total_allowance_gb' => $this->totalAllowanceGb,
            'used_data_gb' => $this->usedDataGb,
            'remaining_data_gb' => $this->remainingDataGb,
            'usage_percentage' => $this->usagePercentage,
        ];
    }
}
