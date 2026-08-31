<?php

namespace Packages\Core\Src\Traits;

use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;
use Packages\Core\Src\Services\WidgetItem;
use Packages\Core\Src\Services\WidgetService;

trait LoadAndPublishDataTrait
{
    protected string $namespace;

    protected string $basePath;

    /**
     * Set the namespace and base path for the package
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        $this->basePath = base_path("packages/{$namespace}");

        return $this;
    }

    /**
     * Load migrations from the package
     */
    public function loadMigrations(): self
    {
        $path = $this->basePath.'/database/migrations';
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }

        return $this;
    }

    /**
     * Load and publish views from the package
     */
    public function loadAndPublishViews(): self
    {
        $path = $this->basePath.'/resources/views';
        if (is_dir($path)) {
            $this->loadViewsFrom($path, strtolower($this->namespace));
        }

        return $this;
    }

    /**
     * Load routes from the package
     */
    public function loadRoutes(array $types = ['web']): self
    {
        foreach ($types as $type) {
            $routeFile = $this->basePath."/routes/{$type}.php";
            if (file_exists($routeFile)) {
                $this->loadRoutesFrom($routeFile);
            }
        }

        return $this;
    }

    /**
     * Load translations from the package
     */
    public function loadAndPublishTranslations(): self
    {
        $path = $this->basePath.'/resources/lang';
        if (is_dir($path)) {
            $this->loadTranslationsFrom($path, strtolower($this->namespace));
        }

        return $this;
    }

    /**
     * Load and merge configurations from the package
     */
    public function loadConfigurations(array $files): self
    {
        foreach ($files as $file) {
            $configPath = $this->basePath."/configs/{$file}.php";
            if (file_exists($configPath)) {
                $this->mergeConfigFrom($configPath, $file);
            }
        }

        return $this;
    }

    /**
     * Get the base path of the package
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Get the namespace of the package
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Register sidebar items for this package
     *
     * @param  SidebarItem[]  $items
     */
    public function registerSidebarItems(array $items): self
    {
        if (class_exists(SidebarService::class)) {
            $sidebar = app(SidebarService::class);
            $sidebar->registerItems($items);
        }

        return $this;
    }

    /**
     * Register dashboard widgets for this package
     *
     * @param  WidgetItem[]  $widgets
     */
    public function registerWidgets(array $widgets): self
    {
        if (class_exists(WidgetService::class)) {
            $widgetService = app(WidgetService::class);
            $widgetService->registerWidgets($widgets);
        }

        return $this;
    }
}
