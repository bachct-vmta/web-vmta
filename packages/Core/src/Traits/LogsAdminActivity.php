<?php

namespace Packages\Core\Src\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Packages\Core\Src\Repositories\Interfaces\ActivityLogRepositoryInterface;

/**
 * Trait for admin controllers to log CRUD operations.
 *
 * Persists to activity_logs table via repository and also writes to file log.
 */
trait LogsAdminActivity
{
    /**
     * Log an admin activity.
     *
     * @param  string  $action  e.g. 'created', 'updated', 'deleted'
     * @param  Model|null  $model  The affected model
     * @param  array  $extra  Optional extra data (keys: 'old', 'new')
     */
    protected function logActivity(string $action, ?Model $model = null, array $extra = []): void
    {
        // Persist to database via repository
        try {
            $repo = app(ActivityLogRepositoryInterface::class);
            $repo->log(
                $action,
                $model ? get_class($model) : null,
                $model?->id,
                $extra['old'] ?? null,
                $extra['new'] ?? null
            );
        } catch (\Throwable $e) {
            // Fallback: if DB write fails, at least log to file
            Log::error('[ActivityLog] Failed to persist: '.$e->getMessage());
        }

        // Also write to file log for redundancy
        Log::channel('single')->info("[Admin] {$action}", [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'model' => $model ? class_basename($model).'#'.$model->id : null,
            'ip' => request()->ip(),
        ]);
    }
}
