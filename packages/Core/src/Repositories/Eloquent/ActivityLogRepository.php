<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Packages\Core\Src\Models\ActivityLog;
use Packages\Core\Src\Repositories\Interfaces\ActivityLogRepositoryInterface;

class ActivityLogRepository extends BaseRepository implements ActivityLogRepositoryInterface
{
    /**
     * Specify Model class name
     */
    public function getModel(): string
    {
        return ActivityLog::class;
    }

    /**
     * Get paginated logs with filters
     */
    public function paginateFiltered(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with('user')->latest();

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get logs for a specific model
     */
    public function getByModel(string $modelType, int $modelId): Collection
    {
        return $this->model
            ->with('user')
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->latest()
            ->get();
    }

    /**
     * Get logs by user
     */
    public function getByUser(int $userId, int $limit = 50): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent logs
     */
    public function getRecent(int $limit = 20): Collection
    {
        return $this->model
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Prune old logs
     */
    public function prune(int $days = 90): int
    {
        return ActivityLog::prune($days);
    }

    /**
     * Log an activity
     */
    public function log(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): Model {
        return $this->model->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
