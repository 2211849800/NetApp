<?php

namespace App\Exceptions\Subscriber;

use Exception;

class RechargeFailedException extends Exception
{
    public static function withReason(string $contractNumber, string $reason): self
    {
        return new self("Recharge operation failed for contract [{$contractNumber}]: {$reason}");
    }
}
