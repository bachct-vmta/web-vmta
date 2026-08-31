<?php

namespace Packages\Newsletter\Src\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;
use Packages\Newsletter\Src\Http\Controllers\Public\ConfirmationController;
use Packages\Newsletter\Src\Http\Controllers\Public\SubscribeController;
use Packages\Newsletter\Src\Repositories\Eloquent\NewsletterRepository;
use Packages\Newsletter\Src\Repositories\Interfaces\NewsletterRepositoryInterface;
use Packages\Newsletter\Src\Services\NewsletterService;
use Spatie\Honeypot\ProtectAgainstSpam;

class NewsletterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../configs/newsletter.php', 'newsletter');
        $this->app->bind(NewsletterRepositoryInterface::class, NewsletterRepository::class);
        $this->app->singleton(NewsletterService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'newsletter');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'newsletter');

        $this->registerPublicRoutes();
        $this->registerAdminRoutes();
        $this->registerSidebarItems();
    }

    protected function registerSidebarItems(): void
    {
        $sidebar = app(SidebarService::class);

        $sidebar->registerItem(new SidebarItem(
            name: 'Newsletter',
            route: 'admin.newsletter.index',
            icon: '',
            permission: 'newsletter.index',
            priority: 70,
            activeRoutePattern: 'admin.newsletter.*',
            materialIcon: 'mail',
            slug: 'newsletter',
        ));
    }

    private function registerAdminRoutes(): void
    {
        Route::prefix(admin_prefix())
            ->middleware(['web', 'auth', 'permission'])
            ->name(admin_route_name())
            ->group(__DIR__.'/../../routes/admin.php');
    }

    /**
     * Footer subscribe POST is locale-scoped to match Inquiry's contact routes;
     * confirm/unsubscribe pages are locale-agnostic (token-based).
     */
    private function registerPublicRoutes(): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => []]));
        $throttle = 'throttle:'.config('newsletter.throttle', '3,60');

        foreach ($locales as $locale) {
            Route::middleware(['web', 'vmta.locale'])
                ->prefix($locale)
                ->name("newsletter.{$locale}.")
                ->group(function () use ($throttle) {
                    Route::middleware([ProtectAgainstSpam::class, $throttle])->group(function () {
                        Route::post('newsletter/subscribe', [SubscribeController::class, 'store'])
                            ->name('subscribe');
                    });
                });
        }

        // Token-based confirm + unsubscribe (no locale prefix — emails are static URLs).
        Route::middleware('web')->group(function () {
            Route::get('newsletter/confirm/{token}', [ConfirmationController::class, 'confirm'])
                ->where('token', '[A-Za-z0-9]+')
                ->name('newsletter.confirm');
            Route::get('newsletter/unsubscribe', [ConfirmationController::class, 'unsubscribe'])
                ->name('newsletter.unsubscribe');
        });
    }
}
