<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalNotification extends Model
{
    protected $table = 'operational_notifications';

    protected $fillable = [
        'user_id', 'contract_number', 'title', 'message', 'type', 'read_at',
        'notification_key', 'created_by',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
