<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Packages\Core\Src\Events\UserCreated;
use Packages\Core\Src\Events\UserDeleted;
use Packages\Core\Src\Events\UserUpdated;
use Packages\Core\Src\Models\User;
use Packages\Core\Src\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Specify Model class name
     */
    public function getModel(): string
    {
        return User::class;
    }

    /**
     * Get paginated users with search and filters
     */
    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('role');

        // Search
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if (! empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        // Filter by status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find user by ID with relationships
     */
    public function findById(int $id): ?User
    {
        return $this->model->with('role')->find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create new user
     */
    public function createUser(array $data): User
    {
        $user = $this->model->create($data);
        event(new UserCreated($user));

        return $user;
    }

    /**
     * Update existing user
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        $user = $user->fresh();
        User::clearPermissionCache($user->id);
        event(new UserUpdated($user));

        return $user;
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): bool
    {
        User::clearPermissionCache($user->id);
        event(new UserDeleted($user));

        return $user->delete();
    }

    /**
     * Get active users count
     */
    public function countActive(): int
    {
        return $this->model->where('is_active', true)->count();
    }

    /**
     * Get users by role
     */
    public function getByRole(int $roleId): Collection
    {
        return $this->model->where('role_id', $roleId)->get();
    }
}
