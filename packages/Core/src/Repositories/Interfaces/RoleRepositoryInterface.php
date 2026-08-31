<?php

namespace Packages\Core\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Packages\Core\Src\Models\Role;

interface RoleRepositoryInterface
{
    /**
     * Get paginated roles with search and filters
     */
    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find role by ID with user count
     */
    public function findById(int $id): ?Role;

    /**
     * Get all roles (for dropdown)
     */
    public function getAll(): Collection;

    /**
     * Create new role
     */
    public function createRole(array $data): Role;

    /**
     * Update existing role
     */
    public function updateRole(Role $role, array $data): Role;

    /**
     * Delete role
     */
    public function deleteRole(Role $role): bool;

    /**
     * Get default role
     */
    public function getDefault(): ?Role;

    /**
     * Clear default from all roles
     */
    public function clearDefault(): int;

    /**
     * Check if role has users
     */
    public function hasUsers(Role $role): bool;

    /**
     * Find role by slug
     */
    public function findBySlug(string $slug): ?Role;
}
