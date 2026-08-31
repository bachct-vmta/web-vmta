<?php

namespace Packages\Core\Src\Services;

class PermissionService
{
    /**
     * All registered permissions from packages
     */
    protected array $permissions = [];

    /**
     * Register permissions from a package config
     */
    public function registerPermissions(array $permissions): void
    {
        $this->permissions = array_merge($this->permissions, $permissions);
    }

    /**
     * Get all registered permissions
     */
    public function getAllPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get permissions grouped by parent flag
     * Returns structure for building permission tree in admin UI
     */
    public function getGroupedPermissions(): array
    {
        $grouped = [];
        $children = [];

        // First pass: identify parents and children
        foreach ($this->permissions as $permission) {
            $flag = $permission['flag'];
            $parentFlag = $permission['parent_flag'] ?? null;

            if (! $parentFlag) {
                // This is a parent permission
                if (! isset($grouped[$flag])) {
                    $grouped[$flag] = [
                        'name' => $permission['name'],
                        'flag' => $flag,
                        'children' => [],
                    ];
                } else {
                    $grouped[$flag]['name'] = $permission['name'];
                }
            } else {
                // This is a child permission, store for later
                $children[] = $permission;
            }
        }

        // Second pass: assign children to parents
        foreach ($children as $child) {
            $parentFlag = $child['parent_flag'];

            if (! isset($grouped[$parentFlag])) {
                // Create parent if it doesn't exist
                $grouped[$parentFlag] = [
                    'name' => $parentFlag,
                    'flag' => $parentFlag,
                    'children' => [],
                ];
            }

            $grouped[$parentFlag]['children'][] = [
                'name' => $child['name'],
                'flag' => $child['flag'],
            ];
        }

        return $grouped;
    }

    /**
     * Get all permission flags as a flat array
     */
    public function getAllFlags(): array
    {
        return array_column($this->permissions, 'flag');
    }

    /**
     * Check if a permission flag exists
     */
    public function exists(string $flag): bool
    {
        return in_array($flag, $this->getAllFlags());
    }

    /**
     * Get permission by flag
     */
    public function getByFlag(string $flag): ?array
    {
        foreach ($this->permissions as $permission) {
            if ($permission['flag'] === $flag) {
                return $permission;
            }
        }

        return null;
    }
}
