<?php

namespace Packages\Core\Src\Services;

/**
 * Service to manage sidebar menu items across packages
 *
 * This service allows packages to register their own sidebar items
 * without modifying the Core admin layout directly.
 */
class SidebarService
{
    /**
     * Registered sidebar items
     */
    protected array $items = [];

    /**
     * Register a single sidebar item
     */
    public function registerItem(SidebarItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Register multiple sidebar items at once
     *
     * @param  SidebarItem[]  $items
     */
    public function registerItems(array $items): self
    {
        foreach ($items as $item) {
            if ($item instanceof SidebarItem) {
                $this->registerItem($item);
            }
        }

        return $this;
    }

    /**
     * Get all registered items sorted by priority (flat list)
     *
     * @return SidebarItem[]
     */
    public function getItems(): array
    {
        return collect($this->items)
            ->sortBy('priority')
            ->values()
            ->all();
    }

    /**
     * Build hierarchical structure from flat items
     * Items with parentSlug will be grouped under their parent
     *
     * @return SidebarItem[]
     */
    public function buildHierarchy(): array
    {
        $items = $this->getItems();
        $parentMap = [];
        $rootItems = [];

        // First pass: identify parents and collect children by parentSlug
        foreach ($items as $item) {
            if ($item->parentSlug) {
                // This item should be a child of another item
                if (! isset($parentMap[$item->parentSlug])) {
                    $parentMap[$item->parentSlug] = [];
                }
                $parentMap[$item->parentSlug][] = $item;
            } else {
                // This is a root item (may or may not have inline children)
                $rootItems[] = $item;
            }
        }

        // Second pass: attach children to parents
        foreach ($rootItems as $item) {
            if ($item->slug && isset($parentMap[$item->slug])) {
                foreach ($parentMap[$item->slug] as $child) {
                    $item->addChild($child);
                }
            }
        }

        // Sort children by priority
        foreach ($rootItems as $item) {
            if ($item->hasChildren()) {
                usort($item->children, fn ($a, $b) => $a->priority <=> $b->priority);
            }
        }

        return $rootItems;
    }

    /**
     * Get all visible items for the current user (hierarchical)
     *
     * @return SidebarItem[]
     */
    public function getVisibleItems(): array
    {
        $items = $this->buildHierarchy();

        return collect($items)
            ->filter(function (SidebarItem $item) {
                // If item has children, check if any child is visible
                if ($item->hasChildren()) {
                    $visibleChildren = $item->getVisibleChildren();
                    if (count($visibleChildren) === 0) {
                        return false; // Hide parent if no children are visible
                    }
                    // Update children with only visible ones
                    $item->children = $visibleChildren;
                }

                return $item->canView();
            })
            ->values()
            ->all();
    }

    /**
     * Clear all registered items (useful for testing)
     */
    public function clear(): self
    {
        $this->items = [];

        return $this;
    }
}
