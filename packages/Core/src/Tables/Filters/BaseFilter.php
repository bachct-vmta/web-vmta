<?php

namespace Packages\Core\Src\Tables\Filters;

use Packages\Core\Src\Tables\Contracts\FilterInterface;

/**
 * Base filter class with common functionality
 */
abstract class BaseFilter implements FilterInterface
{
    protected string $name;

    protected ?string $label = null;

    protected ?string $column = null;

    protected mixed $default = null;

    /**
     * Create a new filter instance
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
     * Get the filter name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the database column (defaults to filter name)
     */
    public function getColumn(): string
    {
        return $this->column ?? $this->name;
    }

    /**
     * Set the database column
     */
    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Get the filter label
     */
    public function getLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        $label = str_replace(['_', '.'], ' ', $this->name);

        return ucwords($label);
    }

    /**
     * Set the filter label
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set default value
     */
    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get the filter's current value from request
     */
    public function getValue(): mixed
    {
        return request()->get($this->name, $this->default);
    }

    /**
     * Check if filter has a value
     */
    public function hasValue(): bool
    {
        $value = $this->getValue();

        return $value !== null && $value !== '';
    }
}
