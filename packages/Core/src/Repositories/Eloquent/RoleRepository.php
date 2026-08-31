<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Packages\Core\Src\Events\RoleChanged;
use Packages\Core\Src\Models\Role;
use Packages\Core\Src\Models\User;
use Packages\Core\Src\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    /**
     * Specify Model class name
     */
    public function getModel(): string
    {
        return Role::class;
    }

    /**
     * Get paginated roles with search and filters
     */
    public function paginateFiltered(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->withCount('users');

        // Search
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find role by ID with user count
     */
    public function findById(int $id): ?Role
    {
        return $this->model->withCount('users')->find($id);
    }

    /**
     * Get all roles (for dropdown)
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Create new role
     */
    public function createRole(array $data): Role
    {
        $role = $this->model->create($data);
        event(new RoleChanged($role, 'created'));

        return $role;
    }

    /**
     * Update existing role
     */
    public function updateRole(Role $role, array $data): Role
    {
        $role->update($data);
        $role = $role->fresh();

        // Invalidate permission cache for all users with this role
        $this->clearRoleUsersPermissionCache($role->id);

        event(new RoleChanged($role, 'updated'));

        return $role;
    }

    /**
     * Delete role
     */
    public function deleteRole(Role $role): bool
    {
        // Invalidate permission cache for all users with this role
        $this->clearRoleUsersPermissionCache($role->id);

        event(new RoleChanged($role, 'deleted'));

        return $role->delete();
    }

    /**
     * Clear permission cache for all users of a given role
     */
    private function clearRoleUsersPermissionCache(int $roleId): void
    {
        $userIds = User::where('role_id', $roleId)->pluck('id');
        foreach ($userIds as $userId) {
            User::clearPermissionCache($userId);
        }
    }

    /**
     * Get default role
     */
    public function getDefault(): ?Role
    {
        return $this->model->where('is_default', true)->first();
    }

    /**
     * Clear default from all roles
     */
    public function clearDefault(): int
    {
        return $this->model->where('is_default', true)->update(['is_default' => false]);
    }

    /**
     * Check if role has users
     */
    public function hasUsers(Role $role): bool
    {
        return $role->users()->count() > 0;
    }

    /**
     * Find role by slug
     */
    public function findBySlug(string $slug): ?Role
    {
        return $this->model->where('slug', $slug)->first();
    }
}
