<?php

namespace App\DTOs\Subscriber;

readonly class PackageChangeResult
{
    public function __construct(
        public bool $success,
        public string $contractNumber,
        public string $oldPackageId,
        public string $newPackageId,
        public string $newPackageName,
        public ?string $message = null,
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'contract_number' => $this->contractNumber,
            'old_package_id' => $this->oldPackageId,
            'new_package_id' => $this->newPackageId,
            'new_package_name' => $this->newPackageName,
            'message' => $this->message,
        ];
    }
}
