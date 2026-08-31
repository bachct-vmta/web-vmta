<?php

namespace Packages\Core\Src\Models;

/**
 * ActivityLog Model
 *
 * Tracks admin user actions for audit trail.
 */
class ActivityLog extends BaseModel
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected array $searchable = ['action', 'model_type'];

    protected array $filterable = ['user_id', 'action', 'model_type'];

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the related model (polymorphic).
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }

        return null;
    }

    /**
     * Scope: filter by model type.
     */
    public function scopeForModel($query, string $modelClass)
    {
        return $query->where('model_type', $modelClass);
    }

    /**
     * Scope: filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Prune old records.
     */
    public static function prune(int $days = 90): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}
