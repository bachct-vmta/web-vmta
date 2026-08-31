<?php

namespace Packages\Core\Src\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

abstract class BaseService
{
    protected RepositoryInterface $repository;

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function find(int $id): ?Model
    {
        return $this->repository->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data = $this->beforeCreate($data);
            $model = $this->repository->create($data);
            $this->afterCreate($model);

            return $model;
        });
    }

    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $data = $this->beforeUpdate($id, $data);
            $result = $this->repository->update($id, $data);
            $model = $this->repository->find($id);
            if ($model) {
                $this->afterUpdate($model);
            }

            return $result;
        });
    }

    public function delete(int $id): bool
    {
        $model = $this->repository->find($id);

        return DB::transaction(function () use ($id, $model) {
            $result = $this->repository->delete($id);
            if ($result && $model) {
                $this->afterDelete($model);
            }

            return $result;
        });
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function paginateWithFilters(array $filters = [], ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateWithFilters($filters, $search, $sortBy, $sortDir, $perPage);
    }

    /**
     * Hook: called before a model is created.
     * Override in subclasses to transform/validate data before persistence.
     */
    protected function beforeCreate(array $data): array
    {
        return $data;
    }

    /**
     * Hook: called before a model is updated.
     * Override in subclasses to transform/validate data before persistence.
     */
    protected function beforeUpdate(int $id, array $data): array
    {
        return $data;
    }

    /**
     * Hook: called after a model is created.
     * Override in subclasses for post-create logic (events, logging, etc.)
     */
    protected function afterCreate(Model $model): void {}

    /**
     * Hook: called after a model is updated.
     */
    protected function afterUpdate(Model $model): void {}

    /**
     * Hook: called after a model is deleted.
     */
    protected function afterDelete(Model $model): void {}
}
