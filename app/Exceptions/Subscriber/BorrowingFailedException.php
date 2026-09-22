<?php

namespace App\Exceptions\Subscriber;

use Exception;

class BorrowingFailedException extends Exception
{
    public static function withReason(string $contractNumber, string $reason): self
    {
        return new self("تعذر تفعيل سلفة الطوارئ للعقد [{$contractNumber}]: {$reason}");
    }
}
