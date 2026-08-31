<?php

namespace Packages\Localization\Src;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Packages\Localization\Src\Http\Middleware\SetLocaleFromRoute;

class LocalizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'localization');

        Blade::componentNamespace(
            'Packages\\Localization\\Src\\View\\Components',
            'localization'
        );

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('vmta.locale', SetLocaleFromRoute::class);
    }
}
