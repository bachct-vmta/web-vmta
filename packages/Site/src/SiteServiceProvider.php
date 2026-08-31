<?php

namespace Packages\Site\Src;

use Illuminate\Support\ServiceProvider;
use Packages\Site\Src\Services\LocaleUrlService;

class SiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LocaleUrlService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'site');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'site');

        $this->registerPublicRoutes();
    }

    /**
     * Locale prefix groups are reserved here for future Site-only routes (about, contact pages
     * served directly from Site templates). The /{locale}/ home was previously a placeholder —
     * Content package now owns it via HomeController (see Phase 2 Slice 2C1).
     */
    protected function registerPublicRoutes(): void
    {
        // Intentionally empty for now — Content owns /, /tin-tuc, /{slug}; Catalog will own
        // its own routes later. Leaving the method in place documents the intent and gives
        // the Site package a hook to mount future Site-specific public routes.
    }
}
