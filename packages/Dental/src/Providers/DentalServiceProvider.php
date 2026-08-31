<?php

namespace Packages\Dental\Src\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Packages\Dental\Src\Models\DentalService;
use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;
use Packages\Dental\Src\Repositories\Eloquent\DentalCategoryRepository;
use Packages\Dental\Src\Repositories\Eloquent\DentalFacilityRepository;
use Packages\Dental\Src\Repositories\Eloquent\DentalServiceRepository;
use Packages\Dental\Src\Repositories\Interfaces\DentalCategoryRepositoryInterface;
use Packages\Dental\Src\Repositories\Interfaces\DentalFacilityRepositoryInterface;
use Packages\Dental\Src\Repositories\Interfaces\DentalServiceRepositoryInterface;

class DentalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../configs/dental.php', 'dental');

        $this->app->bind(DentalCategoryRepositoryInterface::class, DentalCategoryRepository::class);
        $this->app->bind(DentalFacilityRepositoryInterface::class, DentalFacilityRepository::class);
        $this->app->bind(DentalServiceRepositoryInterface::class, DentalServiceRepository::class);

        // Đăng ký ở register() chứ không phải boot(): Content mount catch-all `{locale}/{slug}`
        // trong boot() và mọi register() chạy trước, nếu không /kham-nha sẽ bị nuốt thành trang Page
        $this->registerPublicRoutes();
    }

    public function boot(): void
    {
        $this->registerMorphMap();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'dental');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'dental');
        $this->registerAdminRoutes();
        $this->registerSidebarItems();

        $this->publishes([
            __DIR__.'/../../configs/dental.php' => config_path('dental.php'),
        ], 'dental-config');
    }

    /**
     * Alias đăng ký từ phía Dental để packages/Inquiry không phải import class của package này.
     * Laravel gộp các lần gọi morphMap nên không đè map của package khác.
     */
    private function registerMorphMap(): void
    {
        Relation::morphMap([
            'dental_service' => DentalService::class,
        ]);
    }

    private function registerAdminRoutes(): void
    {
        Route::prefix(admin_prefix())
            ->middleware(['web', 'auth', 'permission'])
            ->name(admin_route_name())
            ->group(__DIR__.'/../../routes/admin.php');
    }

    /**
     * Mount public routes per locale, mirroring CatalogServiceProvider so names follow
     * `site.{locale}.dental.{action}`.
     */
    private function registerPublicRoutes(): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => []]));
        $slugs = config('dental.url_slug', []);

        foreach ($locales as $locale) {
            $slug = $slugs[$locale] ?? $slugs['vi'] ?? 'kham-nha';

            Route::middleware(['web', 'vmta.locale'])
                ->prefix($locale)
                ->name("site.{$locale}.dental.")
                ->group(function () use ($slug) {
                    require __DIR__.'/../../routes/public.php';
                });
        }
    }

    private function registerSidebarItems(): void
    {
        $group = new SidebarItem(
            name: 'Khám nha',
            route: '',
            icon: '',
            permission: 'dental.index',
            priority: 45,
            materialIcon: 'dentistry',
            slug: 'dental',
        );

        $group->addChild(new SidebarItem('Danh mục khám nha', 'admin.dental_categories.index', '', 'dental.index', 46, 'admin.dental_categories.*', 'category', 'dental-categories'));
        $group->addChild(new SidebarItem('Đối tác', 'admin.dental_facilities.index', '', 'dental.index', 47, 'admin.dental_facilities.*', 'local_hospital', 'dental-facilities'));
        $group->addChild(new SidebarItem('Dịch vụ', 'admin.dental_services.index', '', 'dental.index', 48, 'admin.dental_services.*', 'medical_services', 'dental-services'));

        app(SidebarService::class)->registerItem($group);
    }
}
