<?php

namespace Packages\Core\Src\Tables\Columns;

use Closure;
use Packages\Core\Src\Tables\Contracts\ColumnInterface;

/**
 * Base column class with common functionality
 */
abstract class BaseColumn implements ColumnInterface
{
    protected string $name;

    protected ?string $label = null;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected bool $visible = true;

    protected ?Closure $formatCallback = null;

    protected ?string $alignment = null;

    protected bool $toggleable = false;

    protected bool $hiddenByDefault = false;

    protected bool $html = false;

    /**
     * Create a new column instance
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Static factory method
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Get the column name (database field)
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the column label
     */
    public function getLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        // Convert snake_case or dot.notation to Title Case
        $label = str_replace(['.', '_'], ' ', $this->name);

        return ucwords($label);
    }

    /**
     * Set the column label
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Make the column sortable
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Check if column is sortable
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Make the column searchable
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Check if column is searchable
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Hide the column
     */
    public function hidden(bool $hidden = true): static
    {
        $this->visible = ! $hidden;

        return $this;
    }

    /**
     * Show the column (opposite of hidden)
     */
    public function visible(bool $visible = true): static
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Check if column is visible
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Set column alignment
     */
    public function alignLeft(): static
    {
        $this->alignment = 'left';

        return $this;
    }

    public function alignCenter(): static
    {
        $this->alignment = 'center';

        return $this;
    }

    public function alignRight(): static
    {
        $this->alignment = 'right';

        return $this;
    }

    /**
     * Get alignment class
     */
    public function getAlignmentClass(): string
    {
        return match ($this->alignment) {
            'center' => 'text-center',
            'right' => 'text-right',
            default => 'text-left',
        };
    }

    /**
     * Make the column toggleable (user can show/hide)
     */
    public function toggleable(bool $toggleable = true, bool $hiddenByDefault = false): static
    {
        $this->toggleable = $toggleable;
        $this->hiddenByDefault = $hiddenByDefault;

        return $this;
    }

    /**
     * Format value using callback
     */
    public function formatStateUsing(Closure $callback): static
    {
        $this->formatCallback = $callback;

        return $this;
    }

    /**
     * Render as HTML (be careful with XSS!)
     */
    public function html(bool $html = true): static
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get the raw value from record (supports dot notation)
     */
    protected function getState($record): mixed
    {
        $value = $record;
        $segments = explode('.', $this->name);

        foreach ($segments as $segment) {
            if (is_object($value)) {
                $value = $value->{$segment} ?? null;
            } elseif (is_array($value)) {
                $value = $value[$segment] ?? null;
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Format the value (can be overridden)
     */
    protected function formatValue($value, $record = null): string
    {
        if ($this->formatCallback) {
            return (string) call_user_func($this->formatCallback, $value, $record);
        }

        return (string) ($value ?? '');
    }

    /**
     * Render the column value for a record
     */
    public function render($record): string
    {
        $value = $this->getState($record);

        return $this->formatValue($value, $record);
    }
}
