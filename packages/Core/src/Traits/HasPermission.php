<?php

namespace Packages\Core\Src\Traits;

use Illuminate\Support\Facades\Cache;

trait HasPermission
{
    /**
     * Check if user has permission
     * Priority: super_user > user permissions > role permissions
     */
    public function hasPermission(string $flag): bool
    {
        // Super user has all permissions
        if ($this->isSuperUser()) {
            return true;
        }

        // Check user-specific permissions first
        $userPermissions = $this->permissions ?? [];
        if (isset($userPermissions[$flag])) {
            return $userPermissions[$flag] === true;
        }

        // Fallback to role permissions
        if ($this->role) {
            return $this->role->hasPermission($flag);
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $flags): bool
    {
        foreach ($flags as $flag) {
            if ($this->hasPermission($flag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $flags): bool
    {
        foreach ($flags as $flag) {
            if (! $this->hasPermission($flag)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update user-specific permission
     */
    public function updatePermission(string $flag, bool $value): self
    {
        $permissions = $this->permissions ?? [];
        $permissions[$flag] = $value;
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Remove user-specific permission (fallback to role permission)
     */
    public function removePermission(string $flag): self
    {
        $permissions = $this->permissions ?? [];
        unset($permissions[$flag]);
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Check if user is super user
     */
    public function isSuperUser(): bool
    {
        return $this->is_super_user === true;
    }

    /**
     * Get all effective permissions (merged from role and user)
     * Cached per user for 1 hour
     */
    public function getAllPermissions(): array
    {
        return Cache::remember(
            "user_permissions_{$this->id}",
            3600,
            function () {
                $rolePermissions = $this->role ? ($this->role->permissions ?? []) : [];
                $userPermissions = $this->permissions ?? [];

                return array_merge($rolePermissions, $userPermissions);
            }
        );
    }

    /**
     * Clear permission cache for a specific user
     */
    public static function clearPermissionCache(int $userId): void
    {
        Cache::forget("user_permissions_{$userId}");
    }

    /**
     * Assign a role to the user
     */
    public function assignRole($role): self
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }

        if ($role) {
            $this->role_id = $role->id;
        }

        return $this;
    }
}
