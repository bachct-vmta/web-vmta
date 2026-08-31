<?php

namespace Packages\Core\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Packages\Core\Src\Models\User;

interface UserRepositoryInterface
{
    /**
     * Get paginated users with search and filters
     */
    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find user by ID with relationships
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create new user
     */
    public function createUser(array $data): User;

    /**
     * Update existing user
     */
    public function updateUser(User $user, array $data): User;

    /**
     * Delete user
     */
    public function deleteUser(User $user): bool;

    /**
     * Get active users count
     */
    public function countActive(): int;

    /**
     * Get users by role
     */
    public function getByRole(int $roleId): Collection;
}
