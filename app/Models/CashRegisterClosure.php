<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegisterClosure extends Model
{
    protected $fillable = [
        'user_id',
        'total_cash',
        'total_card',
        'total_transfers',
        'grand_total',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_cash' => 'decimal:2',
            'total_card' => 'decimal:2',
            'total_transfers' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CashRegisterEntry::class, 'closed_in_id');
    }
}
