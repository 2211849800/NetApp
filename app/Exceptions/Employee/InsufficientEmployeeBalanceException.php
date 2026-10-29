<?php

namespace App\Exceptions\Employee;

use RuntimeException;

class InsufficientEmployeeBalanceException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('رصيد الموظف غير كافٍ لإتمام شحن المشترك.');
    }
}
