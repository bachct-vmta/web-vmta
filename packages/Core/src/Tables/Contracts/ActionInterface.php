<?php

namespace Packages\Core\Src\Tables\Contracts;

/**
 * Contract for table actions
 */
interface ActionInterface
{
    /**
     * Get the action name
     */
    public function getName(): string;

    /**
     * Get the action label
     */
    public function getLabel(): string;

    /**
     * Check if action is visible for a record
     */
    public function isVisible($record): bool;

    /**
     * Get the action URL for a record
     */
    public function getUrl($record): string;

    /**
     * Render the action button HTML
     */
    public function render($record): string;
}
