<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action
     */
    public static function log(
        string $action,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a model update with automatic diff
     */
    public static function logUpdate(Model $model, array $oldAttributes, array $newAttributes, ?int $userId = null): AuditLog
    {
        $oldValues = [];
        $newValues = [];

        foreach ($newAttributes as $key => $value) {
            if (isset($oldAttributes[$key]) && $oldAttributes[$key] != $value) {
                $oldValues[$key] = $oldAttributes[$key];
                $newValues[$key] = $value;
            }
        }

        return self::log('updated', $model, $oldValues, $newValues, $userId);
    }

    /**
     * Log a model creation
     */
    public static function logCreate(Model $model, ?int $userId = null): AuditLog
    {
        return self::log('created', $model, [], $model->toArray(), $userId);
    }

    /**
     * Log a model deletion
     */
    public static function logDelete(Model $model, ?int $userId = null): AuditLog
    {
        return self::log('deleted', $model, $model->toArray(), [], $userId);
    }
}
