<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'user_id',
        'contract_number',
        'subscriber_name',
        'subject',
        'type',
        'priority',
        'description',
        'status',
        'internal_notes',
        'staff_reply',
        'assigned_to',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'new', 'under_review', 'in_progress']);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'مفتوحة',
            'in_progress' => 'قيد المعالجة',
            'resolved' => 'تم الحل',
            'closed' => 'مغلقة',
            default => $this->status,
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'high' => 'عالية',
            'low' => 'منخفضة',
            default => 'متوسطة',
        };
    }
}
