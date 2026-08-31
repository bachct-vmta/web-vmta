<?php

namespace Packages\Core\Src\Tables\Actions;

use Closure;
use Packages\Core\Src\Tables\Contracts\ActionInterface;

/**
 * Row action class
 */
class Action implements ActionInterface
{
    protected string $name;

    protected ?string $label = null;

    protected ?string $icon = null;

    protected ?string $route = null;

    protected array $routeParams = [];

    protected ?string $url = null;

    protected string $method = 'GET';

    protected ?string $permission = null;

    protected ?string $confirm = null;

    protected ?Closure $visibleCallback = null;

    protected ?Closure $urlCallback = null;

    protected string $color = 'gray';

    protected bool $openInNewTab = false;

    /**
     * Create a new action instance
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
        if ($this->label) {
            return $this->label;
        }

        return ucfirst($this->name);
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
     * Set the action icon (SVG string)
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Use a preset icon
     */
    public function iconEdit(): static
    {
        $this->icon = '<span class="material-symbols-rounded">edit</span>';

        return $this;
    }

    public function iconDelete(): static
    {
        $this->icon = '<span class="material-symbols-rounded">delete</span>';

        return $this;
    }

    public function iconView(): static
    {
        $this->icon = '<span class="material-symbols-rounded">visibility</span>';

        return $this;
    }

    public function iconMore(): static
    {
        $this->icon = '<span class="material-symbols-rounded">more_vert</span>';

        return $this;
    }

    /**
     * Set the route for this action
     */
    public function route(string $route, array $params = []): static
    {
        $this->route = $route;
        $this->routeParams = $params;

        return $this;
    }

    /**
     * Set a static URL
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set URL using a callback
     */
    public function urlUsing(Closure $callback): static
    {
        $this->urlCallback = $callback;

        return $this;
    }

    /**
     * Set HTTP method (for form actions)
     */
    public function method(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Require permission to view this action
     */
    public function permission(string $permission): static
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Add confirmation dialog
     */
    public function confirm(string $message): static
    {
        $this->confirm = $message;

        return $this;
    }

    /**
     * Set visibility callback
     */
    public function visible(Closure $callback): static
    {
        $this->visibleCallback = $callback;

        return $this;
    }

    /**
     * Hide based on callback
     */
    public function hidden(Closure $callback): static
    {
        $this->visibleCallback = fn ($record) => ! $callback($record);

        return $this;
    }

    /**
     * Set action color
     */
    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Danger color (red)
     */
    public function danger(): static
    {
        $this->color = 'danger';

        return $this;
    }

    /**
     * Success color (green)
     */
    public function success(): static
    {
        $this->color = 'success';

        return $this;
    }

    /**
     * Open in new tab
     */
    public function openInNewTab(bool $open = true): static
    {
        $this->openInNewTab = $open;

        return $this;
    }

    /**
     * Check if action is visible for a record
     */
    public function isVisible($record): bool
    {
        // Check permission
        if ($this->permission) {
            if (! auth()->check() || ! auth()->user()->hasPermission($this->permission)) {
                return false;
            }
        }

        // Check visibility callback
        if ($this->visibleCallback) {
            return (bool) call_user_func($this->visibleCallback, $record);
        }

        return true;
    }

    /**
     * Get the action URL for a record
     */
    public function getUrl($record): string
    {
        if ($this->urlCallback) {
            return call_user_func($this->urlCallback, $record);
        }

        if ($this->url) {
            return $this->url;
        }

        if ($this->route) {
            $params = $this->routeParams;

            // Auto-add record as parameter if not specified
            if (empty($params)) {
                // Try to get the route parameter name from the route definition
                try {
                    $routeObj = app('router')->getRoutes()->getByName($this->route);
                    if ($routeObj) {
                        $paramNames = $routeObj->parameterNames();
                        if (! empty($paramNames)) {
                            // Use the first parameter name from route definition
                            $params = [$paramNames[0] => $record];
                        } else {
                            $params = [$record];
                        }
                    } else {
                        $params = [$record];
                    }
                } catch (\Exception $e) {
                    $params = [$record];
                }
            } else {
                // Replace {record} placeholder
                foreach ($params as $key => $value) {
                    if ($value === '{record}') {
                        $params[$key] = $record;
                    }
                }
            }

            return route($this->route, $params);
        }

        return '#';
    }

    /**
     * Get color classes
     */
    protected function getColorClasses(): string
    {
        return match ($this->color) {
            'danger', 'red' => 'hover:bg-slate-100 dark:hover:bg-slate-700 text-red-500 hover:text-red-600',
            'success', 'green' => 'hover:bg-slate-100 dark:hover:bg-slate-700 text-emerald-500 hover:text-emerald-600',
            'warning', 'yellow' => 'hover:bg-slate-100 dark:hover:bg-slate-700 text-amber-500 hover:text-amber-600',
            'primary', 'blue' => 'hover:bg-slate-100 dark:hover:bg-slate-700 text-primary hover:text-blue-600',
            default => 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-primary',
        };
    }

    /**
     * Render the action button HTML
     */
    public function render($record): string
    {
        if (! $this->isVisible($record)) {
            return '';
        }

        $url = $this->getUrl($record);
        $colorClasses = $this->getColorClasses();
        $label = $this->getLabel();
        $target = $this->openInNewTab ? 'target="_blank"' : '';

        // If it's a form action (DELETE, POST, etc.)
        if ($this->method !== 'GET') {
            $confirm = $this->confirm
                ? sprintf('onsubmit="return confirm(\'%s\')"', e($this->confirm))
                : '';

            $iconHtml = $this->icon ?? '';

            return sprintf(
                '<form method="POST" action="%s" class="inline" %s>
                    %s
                    %s
                    <button type="submit" class="p-2 rounded-lg %s" title="%s">
                        %s
                    </button>
                </form>',
                e($url),
                $confirm,
                csrf_field(),
                $this->method !== 'POST' ? method_field($this->method) : '',
                $colorClasses,
                e($label),
                $iconHtml
            );
        }

        // Regular link action
        $iconHtml = $this->icon ?? '';

        return sprintf(
            '<a href="%s" class="p-2 rounded-lg transition-colors %s" title="%s" %s>%s</a>',
            e($url),
            $colorClasses,
            e($label),
            $target,
            $iconHtml
        );
    }
}
