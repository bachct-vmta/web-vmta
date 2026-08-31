<?php

namespace Packages\Core\Src\Tables\Contracts;

/**
 * Contract for table columns
 */
interface ColumnInterface
{
    /**
     * Get the column name (database field)
     */
    public function getName(): string;

    /**
     * Get the column label
     */
    public function getLabel(): string;

    /**
     * Check if column is sortable
     */
    public function isSortable(): bool;

    /**
     * Check if column is searchable
     */
    public function isSearchable(): bool;

    /**
     * Check if column is visible
     */
    public function isVisible(): bool;

    /**
     * Render the column value for a record
     */
    public function render($record): string;
}
