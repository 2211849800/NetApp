<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockSubscriberSession extends Model
{
    protected $fillable = [
        'contract_number', 'session_id', 'ip_address', 'mac_address', 'access_point',
        'started_at', 'ended_at', 'download_gb', 'upload_gb',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'download_gb' => 'decimal:3',
            'upload_gb' => 'decimal:3',
        ];
    }
}
