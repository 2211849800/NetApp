<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'contract_number',
        'subscriber_name',
        'package_id',
        'package_name',
        'amount',
        'payment_method',
        'receipt_path',
        'status',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'معلقة',
            'approved' => 'مقبولة',
            'rejected' => 'مرفوضة',
            default => $this->status,
        };
    }

    public function paymentMethodLabel(): string
    {
        return Payment::GATEWAYS[$this->payment_method] ?? ($this->payment_method ?: '—');
    }
}
