<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function __construct(private ?Request $request = null) {}

    /**
     * Log a generic action.
     */
    public function log(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id'     => $userId ?? auth()->id(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => $this->request?->ip(),
            'user_agent'  => $this->request?->userAgent(),
        ]);
    }

    /**
     * Convenience: log a model change (before/after diff).
     */
    public function logModelChange(string $action, Model $model, array $oldValues): AuditLog
    {
        return $this->log(
            action: $action,
            entityType: $model::class,
            entityId: (string) $model->getKey(),
            oldValues: $oldValues,
            newValues: $model->getAttributes(),
        );
    }

    /**
     * Auth-specific helpers.
     */
    public function logAuth(string $event, ?int $userId = null, array $context = []): AuditLog
    {
        return $this->log(
            action: $event,                // login_success, login_failed, logout, password_reset
            entityType: 'auth',
            entityId: $userId ? (string) $userId : null,
            newValues: $context ?: null,
            userId: $userId,
        );
    }
}
