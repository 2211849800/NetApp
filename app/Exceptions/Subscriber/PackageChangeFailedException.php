<?php

namespace App\Exceptions\Subscriber;

use Exception;

class PackageChangeFailedException extends Exception
{
    public static function withReason(string $contractNumber, string $reason): self
    {
        return new self("Package change failed for contract [{$contractNumber}]: {$reason}");
    }
}
