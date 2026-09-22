<?php

namespace App\Exceptions\Subscriber;

use Exception;

class SubscriberNotFoundException extends Exception
{
    public static function forContract(string $contractNumber): self
    {
        return new self("Subscriber with contract number [{$contractNumber}] was not found.");
    }
}
