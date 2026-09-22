<?php

namespace App\Services\Employee;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @param  array{name: string, username: string, phone?: string|null, email?: string|null, password: string, role: string, status: string}  $data
     */
    public function createStaffMember(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
                'type' => $data['role'] === 'admin' ? 'admin' : 'employee',
                'status' => UserStatus::from($data['status']),
                'is_active' => $data['status'] === 'active',
            ]);

            $user->roles()->sync([]);
            $user->assignRole($data['role']);

            $this->auditLog->log('EMPLOYEE_CREATED', 'User', $user->id, null, [
                'name' => $user->name,
                'username' => $user->username,
                'phone' => $user->phone,
                'email' => $user->email,
                'role' => $data['role'],
                'status' => $data['status'],
            ]);

            return $user->load('roles');
        });
    }

    public function updateStaffMember(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $old = $user->only(['name', 'username', 'phone', 'email', 'status', 'type']);
            $oldRole = $user->roles->pluck('name')->first();

            $user->update([
                'name' => $data['name'],
                'username' => $data['username'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'type' => $data['role'] === 'admin' ? 'admin' : 'employee',
                'status' => UserStatus::from($data['status']),
                'is_active' => $data['status'] === 'active',
            ]);

            $user->roles()->sync([]);
            $user->assignRole($data['role']);

            $this->auditLog->log('EMPLOYEE_UPDATED', 'User', $user->id, array_merge($old, ['role' => $oldRole]), [
                'name' => $user->name,
                'username' => $user->username,
                'phone' => $user->phone,
                'email' => $user->email,
                'role' => $data['role'],
                'status' => $data['status'],
            ]);

            return $user->fresh('roles');
        });
    }

    public function resetPassword(User $user, string $password): void
    {
        DB::transaction(function () use ($user, $password) {
            $user->update(['password' => Hash::make($password)]);

            $this->auditLog->log('PASSWORD_RESET', 'User', $user->id);
        });
    }

    public function updateStatus(User $user, UserStatus $status): User
    {
        return DB::transaction(function () use ($user, $status) {
            $old = $user->status?->value;

            $user->update([
                'status' => $status,
                'is_active' => $status === UserStatus::Active,
            ]);

            $this->auditLog->log('EMPLOYEE_STATUS_CHANGED', 'User', $user->id, ['status' => $old], [
                'status' => $status->value,
            ]);

            return $user->fresh('roles');
        });
    }
}
