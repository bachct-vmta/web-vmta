<?php

namespace Packages\Core\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ActivityLogRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated logs with filters
     */
    public function paginateFiltered(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Get logs for a specific model
     */
    public function getByModel(string $modelType, int $modelId): Collection;

    /**
     * Get logs by user
     */
    public function getByUser(int $userId, int $limit = 50): Collection;

    /**
     * Get recent logs
     */
    public function getRecent(int $limit = 20): Collection;

    /**
     * Prune old logs
     */
    public function prune(int $days = 90): int;

    /**
     * Log an activity
     */
    public function log(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): Model;
}
