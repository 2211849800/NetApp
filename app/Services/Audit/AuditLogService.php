<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    private const SENSITIVE_KEYS = ['password', 'password_confirmation', 'current_password'];

    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $result = 'success',
        ?User $actor = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => ($actor ?? auth()->user())?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'result' => $result,
            'ip_address' => Request::ip(),
        ]);
    }

    public function logModelChange(string $action, Model $model, ?array $oldValues = null, ?User $actor = null): AuditLog
    {
        return $this->log(
            action: $action,
            entityType: class_basename($model),
            entityId: $model->getKey(),
            oldValues: $oldValues,
            newValues: $model->getAttributes(),
            actor: $actor,
        );
    }

    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return collect($values)
            ->except(self::SENSITIVE_KEYS)
            ->map(fn ($value, $key) => in_array($key, self::SENSITIVE_KEYS, true) ? '[REDACTED]' : $value)
            ->all();
    }
}
