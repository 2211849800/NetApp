<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockSubscriber extends Model
{
    protected $table = 'mock_subscribers';

    protected $fillable = [
        'contract_number',
        'name',
        'phone',
        'status',
        'current_package_id',
        'current_package_name',
        'current_package_price',
        'data_allowance_gb',
        'used_data_gb',
        'expires_at',
        'borrowing_status',
        'borrowing_used_gb',
        'borrowing_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'current_package_price' => 'decimal:2',
            'data_allowance_gb' => 'decimal:2',
            'used_data_gb' => 'decimal:2',
            'borrowing_used_gb' => 'decimal:2',
            'expires_at' => 'datetime',
            'borrowing_expires_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeByContract($query, string $contractNumber)
    {
        return $query->where('contract_number', $contractNumber);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function isExpired(): bool
    {
        return $this->status === 'EXPIRED';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'SUSPENDED';
    }

    public function getRemainingDataGb(): float
    {
        return max(0, (float) $this->data_allowance_gb - (float) $this->used_data_gb);
    }

    public function getUsagePercentage(): float
    {
        if ((float) $this->data_allowance_gb <= 0) {
            return 100.0;
        }

        return round(((float) $this->used_data_gb / (float) $this->data_allowance_gb) * 100, 1);
    }
}
