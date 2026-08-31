<?php

namespace Packages\Core\Src\Tables\Columns;

/**
 * Column for displaying badge/tag style values
 */
class BadgeColumn extends BaseColumn
{
    protected array $colors = [];

    protected ?string $defaultColor = 'gray';

    protected array $icons = [];

    /**
     * Set color mappings for values
     *
     * @param  array  $colors  ['value' => 'color', ...]
     *                         Colors: primary, secondary, success, danger, warning, info, gray
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * Set single color for all values
     */
    public function color(string $color): static
    {
        $this->defaultColor = $color;

        return $this;
    }

    /**
     * Set icon mappings for values
     */
    public function icons(array $icons): static
    {
        $this->icons = $icons;

        return $this;
    }

    /**
     * Get color classes for a value
     */
    protected function getColorClasses(string $value): string
    {
        $color = $this->colors[$value] ?? $this->defaultColor;

        return match ($color) {
            'primary' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 ring-1 ring-blue-100 dark:ring-blue-900/30',
            'secondary' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 ring-1 ring-slate-200 dark:ring-slate-600',
            'success', 'green' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/30',
            'danger', 'red' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 ring-1 ring-red-100 dark:ring-red-900/30',
            'warning', 'yellow' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 ring-1 ring-amber-100 dark:ring-amber-900/30',
            'info', 'blue' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 ring-1 ring-blue-100 dark:ring-blue-900/30',
            'purple' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 ring-1 ring-purple-100 dark:ring-purple-900/30',
            'pink' => 'bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400 ring-1 ring-pink-100 dark:ring-pink-900/30',
            'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 ring-1 ring-indigo-100 dark:ring-indigo-900/30',
            default => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 ring-1 ring-slate-200 dark:ring-slate-600',
        };
    }

    /**
     * Format the value
     */
    protected function formatValue($value, $record = null): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Handle enums with label() method
        $label = $value;
        if (is_object($value)) {
            if (method_exists($value, 'label')) {
                $label = $value->label();
            } elseif (property_exists($value, 'value')) {
                $label = $value->value;
            }

            // Get color from enum if it has color() method
            if (method_exists($value, 'color') && ! isset($this->colors[(string) $value->value])) {
                $this->colors[(string) $value->value] = $value->color();
            }
        }

        if ($this->formatCallback) {
            $label = call_user_func($this->formatCallback, $value);
        }

        $stringValue = is_object($value) && property_exists($value, 'value')
            ? (string) $value->value
            : (string) $value;

        $colorClasses = $this->getColorClasses($stringValue);

        return sprintf(
            '<span class="px-3 py-1 rounded-full text-xs font-bold %s">%s</span>',
            $colorClasses,
            e($label)
        );
    }
}
