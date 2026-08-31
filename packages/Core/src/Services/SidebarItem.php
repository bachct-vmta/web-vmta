<?php

namespace Packages\Core\Src\Services;

/**
 * Represents a single sidebar menu item
 */
class SidebarItem
{
    /**
     * Children items for nested menu
     *
     * @var SidebarItem[]
     */
    public array $children = [];

    /**
     * Create a new sidebar item
     *
     * @param  string  $name  Display name of the menu item
     * @param  string  $route  Laravel route name (empty string for parent-only items)
     * @param  string  $icon  SVG icon markup (legacy)
     * @param  string|null  $permission  Permission flag required to view this item
     * @param  int  $priority  Display order (lower = higher priority)
     * @param  string|null  $activeRoutePattern  Route pattern for active state (e.g., 'admin.users.*')
     * @param  string|null  $materialIcon  Material Symbols icon name (e.g., 'dashboard', 'group')
     * @param  string|null  $slug  Unique identifier for grouping (e.g., 'settings', 'roles')
     * @param  string|null  $parentSlug  Parent slug for auto-grouping (alternative to children array)
     * @param  SidebarItem[]  $children  Child items for nested menu
     */
    public function __construct(
        public string $name,
        public string $route,
        public string $icon,
        public ?string $permission = null,
        public int $priority = 100,
        public ?string $activeRoutePattern = null,
        public ?string $materialIcon = null,
        public ?string $slug = null,
        public ?string $parentSlug = null,
        array $children = []
    ) {
        $this->children = $children;
    }

    /**
     * Check if this item has children
     */
    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }

    /**
     * Add a child item
     */
    public function addChild(SidebarItem $child): self
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Check if any child is currently active
     */
    public function isAnyChildActive(): bool
    {
        foreach ($this->children as $child) {
            if ($child->isActive() || $child->isAnyChildActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get visible children for the current user
     *
     * @return SidebarItem[]
     */
    public function getVisibleChildren(): array
    {
        return array_values(array_filter(
            $this->children,
            fn (SidebarItem $child) => $child->canView()
        ));
    }

    /**
     * Check if this item should be active for the current request
     */
    public function isActive(): bool
    {
        $pattern = $this->activeRoutePattern ?? $this->route;

        // Support pipe-separated patterns (e.g., 'route.a|route.b')
        if (str_contains($pattern, '|')) {
            $patterns = explode('|', $pattern);

            return request()->routeIs(...$patterns);
        }

        return request()->routeIs($pattern);
    }

    /**
     * Check if the current user can view this item
     */
    public function canView(): bool
    {
        if (! $this->permission) {
            return true;
        }

        return auth()->check() && auth()->user()->hasPermission($this->permission);
    }
}
