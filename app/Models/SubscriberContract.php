<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriberContract extends Model
{
    protected $fillable = [
        'contract_number', 'name', 'phone', 'national_id', 'email',
        'package_id', 'details', 'created_by',
    ];

    protected function casts(): array
    {
        return ['details' => 'array'];
    }
}
