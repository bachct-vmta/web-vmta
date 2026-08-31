<?php

namespace Packages\Core\Src\Models;

class Role extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_default',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_default' => 'boolean',
    ];

    protected array $searchable = ['name', 'slug', 'description'];

    /**
     * Get users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $flag): bool
    {
        $permissions = $this->permissions ?? [];

        return isset($permissions[$flag]) && $permissions[$flag] === true;
    }

    /**
     * Set a permission for this role
     */
    public function setPermission(string $flag, bool $value): self
    {
        $permissions = $this->permissions ?? [];
        $permissions[$flag] = $value;
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Set multiple permissions at once
     */
    public function setPermissions(array $flags): self
    {
        $permissions = [];
        foreach ($flags as $flag => $value) {
            if (is_int($flag)) {
                $permissions[$value] = true;
            } else {
                $permissions[$flag] = $value;
            }
        }
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Remove a permission from this role
     */
    public function removePermission(string $flag): self
    {
        $permissions = $this->permissions ?? [];
        unset($permissions[$flag]);
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Get all permission flags that are enabled
     */
    public function getEnabledPermissions(): array
    {
        $permissions = $this->permissions ?? [];

        return array_keys(array_filter($permissions, fn ($v) => $v === true));
    }
}
