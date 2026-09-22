<?php

namespace App\Exceptions;

/**
 * Exception for business rule violations.
 *
 * Use this when a business rule prevents an operation:
 * - duplicate recharge attempt
 * - invalid package selection
 * - unauthorized status change
 * - payment already processed
 */
class BusinessException extends ApiException
{
    public function __construct(
        string $message,
        string $errorCode = 'BUSINESS_ERROR',
        int $statusCode = 422,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $errorCode, $statusCode, $previous);
    }
}
