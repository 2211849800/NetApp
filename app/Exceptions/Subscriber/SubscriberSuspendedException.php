<?php

namespace App\Exceptions\Subscriber;

use Exception;

class SubscriberSuspendedException extends Exception
{
    public static function forContract(string $contractNumber): self
    {
        return new self("Subscriber with contract number [{$contractNumber}] is suspended.");
    }
}
