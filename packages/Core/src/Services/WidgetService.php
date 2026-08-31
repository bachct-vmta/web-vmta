<?php

namespace Packages\Core\Src\Services;

/**
 * Service to manage dashboard widgets across packages
 *
 * This service allows packages to register their own dashboard widgets
 * without modifying the Core dashboard view directly.
 */
class WidgetService
{
    /**
     * Registered widgets
     */
    protected array $widgets = [];

    /**
     * Register a single widget
     */
    public function registerWidget(WidgetItem $widget): self
    {
        $this->widgets[] = $widget;

        return $this;
    }

    /**
     * Register multiple widgets at once
     *
     * @param  WidgetItem[]  $widgets
     */
    public function registerWidgets(array $widgets): self
    {
        foreach ($widgets as $widget) {
            if ($widget instanceof WidgetItem) {
                $this->registerWidget($widget);
            }
        }

        return $this;
    }

    /**
     * Get all registered widgets sorted by priority
     *
     * @return WidgetItem[]
     */
    public function getWidgets(): array
    {
        return collect($this->widgets)
            ->sortBy('priority')
            ->values()
            ->all();
    }

    /**
     * Get all visible widgets for the current user
     *
     * @return WidgetItem[]
     */
    public function getVisibleWidgets(): array
    {
        return collect($this->getWidgets())
            ->filter(fn (WidgetItem $widget) => $widget->canView())
            ->values()
            ->all();
    }

    /**
     * Clear all registered widgets (useful for testing)
     */
    public function clear(): self
    {
        $this->widgets = [];

        return $this;
    }
}
