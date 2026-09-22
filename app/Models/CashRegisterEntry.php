<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegisterEntry extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'note',
        'closed_in_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closure(): BelongsTo
    {
        return $this->belongsTo(CashRegisterClosure::class, 'closed_in_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('closed_in_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
