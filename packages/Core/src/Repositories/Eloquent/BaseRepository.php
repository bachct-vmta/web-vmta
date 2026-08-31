<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->getModel());
    }

    /**
     * Get the model class name
     */
    abstract public function getModel(): string;

    /**
     * Get all records
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a record by ID
     */
    public function find($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by ID or fail
     */
    public function findOrFail($id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record
     */
    public function update($id, array $data): bool
    {
        $record = $this->findOrFail($id);

        return $record->update($data);
    }

    /**
     * Delete a record
     */
    public function delete($id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    /**
     * Paginate records
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find by a specific field
     */
    public function findBy(string $field, $value): ?Model
    {
        return $this->model->where($field, $value)->first();
    }

    /**
     * Find all by a specific field
     */
    public function findAllBy(string $field, $value): Collection
    {
        return $this->model->where($field, $value)->get();
    }

    /**
     * Get query builder with filters
     */
    public function query(array $filters = [], ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc')
    {
        $query = $this->model->newQuery();

        if (! empty($filters)) {
            $query->filter($filters);
        }

        if (! empty($search)) {
            $query->search($search);
        }

        if (! empty($sortBy)) {
            $query->sortBy($sortBy, $sortDir);
        }

        return $query;
    }

    /**
     * Get paginated results with filters
     */
    public function paginateWithFilters(array $filters = [], ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters, $search, $sortBy, $sortDir)->paginate($perPage);
    }
}
