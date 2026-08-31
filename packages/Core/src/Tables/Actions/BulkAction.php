<?php

namespace Packages\Core\Src\Tables\Actions;

use Closure;

/**
 * Bulk action for multiple records
 */
class BulkAction
{
    protected string $name;

    protected ?string $label = null;

    protected ?string $icon = null;

    protected ?string $permission = null;

    protected ?string $confirm = null;

    protected ?Closure $action = null;

    protected string $color = 'gray';

    /**
     * Create a new bulk action instance
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
     * Get the action name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the action label
     */
    public function getLabel(): string
    {
        return $this->label ?? ucfirst($this->name);
    }

    /**
     * Set the action label
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the action icon
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Require permission
     */
    public function permission(string $permission): static
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Add confirmation
     */
    public function confirm(string $message): static
    {
        $this->confirm = $message;

        return $this;
    }

    /**
     * Set the action callback
     */
    public function action(Closure $callback): static
    {
        $this->action = $callback;

        return $this;
    }

    /**
     * Set color
     */
    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Danger color
     */
    public function danger(): static
    {
        $this->color = 'danger';

        return $this;
    }

    /**
     * Check if visible
     */
    public function isVisible(): bool
    {
        if ($this->permission) {
            return auth()->check() && auth()->user()->hasPermission($this->permission);
        }

        return true;
    }

    /**
     * Get color classes
     */
    public function getColorClasses(): string
    {
        return match ($this->color) {
            'danger', 'red' => 'bg-red-600 hover:bg-red-700 text-white',
            'success', 'green' => 'bg-green-600 hover:bg-green-700 text-white',
            'primary', 'blue' => 'bg-primary-600 hover:bg-primary-700 text-white',
            default => 'bg-gray-600 hover:bg-gray-700 text-white',
        };
    }

    /**
     * Execute the bulk action
     */
    public function execute(array $recordIds): mixed
    {
        if ($this->action) {
            return call_user_func($this->action, $recordIds);
        }

        return null;
    }

    /**
     * Get confirmation message
     */
    public function getConfirmMessage(): ?string
    {
        return $this->confirm;
    }

    /**
     * Get icon
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Check if danger color
     */
    public function isDanger(): bool
    {
        return $this->color === 'danger' || $this->color === 'red';
    }
}
