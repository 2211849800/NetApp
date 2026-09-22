<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'recharge_request_id',
        'reference',
        'contract_number',
        'subscriber_name',
        'gateway',
        'amount',
        'status',
        'notes',
        'processed_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public const GATEWAYS = [
        'lypay' => 'Lypay',
        'onepay' => 'OnePay',
        'bank_transfer' => 'تحويل بنكي',
        'cash' => 'نقدي',
    ];

    public const STATUSES = [
        'pending' => 'قيد المراجعة',
        'completed' => 'تم بنجاح',
        'failed' => 'فشلت',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rechargeRequest(): BelongsTo
    {
        return $this->belongsTo(RechargeRequest::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function gatewayLabel(): string
    {
        return self::GATEWAYS[$this->gateway] ?? $this->gateway;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
