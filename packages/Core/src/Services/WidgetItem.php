<?php

namespace Packages\Core\Src\Services;

use Closure;

/**
 * Represents a single widget on the dashboard
 */
class WidgetItem
{
    protected string|int|Closure $valueResolver;

    /**
     * Create a new widget item
     *
     * @param  string  $name  Display name of the widget
     * @param  string|int|Closure  $value  Value or callback that returns the value
     * @param  string  $icon  SVG icon markup (legacy)
     * @param  string  $bgColor  Background color class (e.g., 'bg-blue-100')
     * @param  string  $iconColor  Icon color class (e.g., 'text-blue-600')
     * @param  string|null  $permission  Permission flag required to view this widget
     * @param  int  $priority  Display order (lower = higher priority)
     * @param  string|null  $materialIcon  Material Symbols icon name (e.g., 'group', 'article')
     * @param  string|null  $trend  Trend text (e.g., '+12% from last month')
     * @param  bool  $trendPositive  Whether the trend is positive (green) or negative (red)
     */
    public function __construct(
        public string $name,
        string|int|Closure $value,
        public string $icon,
        public string $bgColor = 'bg-gray-100',
        public string $iconColor = 'text-gray-600',
        public ?string $permission = null,
        public int $priority = 100,
        public ?string $materialIcon = null,
        public ?string $trend = null,
        public bool $trendPositive = true
    ) {
        $this->valueResolver = $value;
    }

    /**
     * Get the widget value (resolves callback if needed)
     */
    public function getValue(): string|int
    {
        if ($this->valueResolver instanceof Closure) {
            try {
                return ($this->valueResolver)();
            } catch (\Exception $e) {
                return 0;
            }
        }

        return $this->valueResolver;
    }

    /**
     * Check if the current user can view this widget
     */
    public function canView(): bool
    {
        if (! $this->permission) {
            return true;
        }

        return auth()->check() && auth()->user()->hasPermission($this->permission);
    }
}
