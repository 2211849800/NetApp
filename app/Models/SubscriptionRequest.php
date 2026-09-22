<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'applicant_name',
        'national_id',
        'phone',
        'email',
        'city',
        'address',
        'package_id',
        'package_name',
        'status',
        'internal_notes',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
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

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'معلقة',
            'under_review' => 'قيد المراجعة',
            'approved' => 'مقبولة',
            'rejected' => 'مرفوضة',
            default => $this->status,
        };
    }
}
