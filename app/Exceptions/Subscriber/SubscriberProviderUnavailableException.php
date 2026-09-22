<?php

namespace App\Exceptions\Subscriber;

use Exception;

class SubscriberProviderUnavailableException extends Exception
{
    public static function timeout(string $provider = 'Mock'): self
    {
        return new self("The external subscriber provider [{$provider}] timed out.");
    }

    public static function serverError(string $provider = 'Mock', ?string $message = null): self
    {
        return new self("The external subscriber provider [{$provider}] returned a server error: " . ($message ?? 'Unknown error'));
    }
}
