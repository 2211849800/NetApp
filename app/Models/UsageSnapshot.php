<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'usage_date',
        'used_gb',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'used_gb' => 'decimal:2',
    ];
}
