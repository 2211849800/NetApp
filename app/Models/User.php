<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Traits\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    public const STAFF_ROLES = ['admin', 'employee', 'supervisor'];

    protected $fillable = [
        'name',
        'username',
        'contract_number',
        'email',
        'phone',
        'password',
        'type',
        'status',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isDirty('status') && $user->status instanceof UserStatus) {
                $user->is_active = $user->status === UserStatus::Active;
            }
        });
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSubscribers($query)
    {
        return $query->where('type', 'subscriber');
    }

    public function scopeEmployees($query)
    {
        return $query->where('type', 'employee');
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('type', ['employee', 'admin']);
    }

    // ──────────────────────────────────────────────
    // Status & Access Helpers
    // ──────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function canLogin(): bool
    {
        return $this->status?->canLogin() ?? false;
    }

    public function isSubscriber(): bool
    {
        return $this->type === 'subscriber';
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('supervisor');
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(self::STAFF_ROLES);
    }

    public function primaryStaffRole(): ?string
    {
        $this->loadMissing('roles');

        foreach (['admin', 'supervisor', 'employee'] as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }

        return null;
    }
}
