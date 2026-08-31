<?php

namespace Packages\Core\Src\Tables\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Contract for table filters
 */
interface FilterInterface
{
    /**
     * Get the filter name
     */
    public function getName(): string;

    /**
     * Get the filter label
     */
    public function getLabel(): string;

    /**
     * Apply the filter to a query
     */
    public function apply(Builder $query, $value): Builder;

    /**
     * Get the filter's current value from request
     */
    public function getValue(): mixed;

    /**
     * Render the filter input HTML
     */
    public function render(): string;
}
