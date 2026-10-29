<?php

namespace App\Services\Employee;

use App\Models\EmployeeWalletTransaction;
use App\Models\User;
use App\Exceptions\Employee\InsufficientEmployeeBalanceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeWalletService
{
    public function balance(User|int $employee): float
    {
        $employeeId = $employee instanceof User ? $employee->id : $employee;
        $credits = EmployeeWalletTransaction::query()->where('employee_id', $employeeId)->where('type', 'credit')->sum('amount');
        $debits = EmployeeWalletTransaction::query()->where('employee_id', $employeeId)->where('type', 'debit')->sum('amount');

        return round((float) $credits - (float) $debits, 2);
    }

    public function credit(User $employee, User $actor, float $amount, ?string $note = null): EmployeeWalletTransaction
    {
        return DB::transaction(function () use ($employee, $actor, $amount, $note) {
            User::query()->whereKey($employee->id)->lockForUpdate()->firstOrFail();

            return EmployeeWalletTransaction::query()->create([
                'employee_id' => $employee->id,
                'actor_id' => $actor->id,
                'type' => 'credit',
                'amount' => $amount,
                'reference' => 'WALLET-' . Str::upper(Str::random(16)),
                'note' => $note,
            ]);
        });
    }

    public function debit(User $employee, float $amount, string $note): EmployeeWalletTransaction
    {
        return DB::transaction(function () use ($employee, $amount, $note) {
            User::query()->whereKey($employee->id)->lockForUpdate()->firstOrFail();
            if ($this->balance($employee) < $amount) {
                throw new InsufficientEmployeeBalanceException();
            }

            return EmployeeWalletTransaction::query()->create([
                'employee_id' => $employee->id,
                'actor_id' => $employee->id,
                'type' => 'debit',
                'amount' => $amount,
                'reference' => 'WALLET-' . Str::upper(Str::random(16)),
                'note' => $note,
            ]);
        });
    }

    public function refund(User $employee, float $amount, string $note): void
    {
        $this->credit($employee, $employee, $amount, $note);
    }
}
