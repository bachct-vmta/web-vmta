<?php

namespace Packages\Catalog\Src\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Packages\Catalog\Src\Http\View\Composers\AlliancePartnerListComposer;
use Packages\Catalog\Src\Models\Combo;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Models\Service;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\TourPackage;
use Packages\Catalog\Src\Repositories\Eloquent\ComboRepository;
use Packages\Catalog\Src\Repositories\Eloquent\DestinationRepository;
use Packages\Catalog\Src\Repositories\Eloquent\PartnerRepository;
use Packages\Catalog\Src\Repositories\Eloquent\ServiceRepository;
use Packages\Catalog\Src\Repositories\Eloquent\SpecialtyRepository;
use Packages\Catalog\Src\Repositories\Eloquent\TourPackageRepository;
use Packages\Catalog\Src\Console\Commands\CrawlWpSpecialtyCommand;
use Packages\Catalog\Src\Repositories\Interfaces\ComboRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\DestinationRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\PartnerRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\ServiceRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\SpecialtyRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\TourPackageRepositoryInterface;
use Packages\Catalog\Src\Services\CatalogService;
use Packages\Catalog\Src\Services\FilterService;
use Packages\Catalog\Src\Services\SearchService;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\BrowserSnapshotRunner;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\LlmExtractor;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\MediaImporter;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\SpecialtyImporter;
use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../configs/catalog.php', 'catalog');

        $this->bindRepositories();
        $this->bindServices();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'catalog');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'catalog');
        $this->registerMorphMap();
        $this->registerAdminRoutes();
        $this->registerPublicRoutes();
        $this->registerSidebarItems();
        $this->registerViewComposers();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CrawlWpSpecialtyCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../../configs/catalog.php' => config_path('catalog.php'),
        ], 'catalog-config');
    }

    /**
     * Feeds Catalog data into partials that other packages include, so those
     * packages never have to reference Catalog classes themselves.
     */
    private function registerViewComposers(): void
    {
        View::composer('catalog::public.partials.alliance-partner-list', AlliancePartnerListComposer::class);
    }

    /**
     * Mount admin routes inside Core's admin group (auth + permission middleware).
     * Each route also declares its own permission flag (workaround for default middleware
     * which derives the flag from the route name).
     */
    private function registerAdminRoutes(): void
    {
        Route::prefix(admin_prefix())
            ->middleware(['web', 'auth', 'permission'])
            ->name(admin_route_name())
            ->group(__DIR__.'/../../routes/admin.php');
    }

    /**
     * Mount public routes per locale, mirroring ContentServiceProvider so route names follow
     * `site.{locale}.catalog.{entity}.{action}` and Tag/Search routes (Slice C2) can shadow
     * any future Site-package placeholders without collision.
     */
    private function registerPublicRoutes(): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => []]));

        // Per-locale URL slug map. Defaults to VN slug if a locale entry is omitted.
        // Add new overrides here when product asks for an EN-specific URL.
        $slugMap = [
            'vi' => [
                'services'    => 'dich-vu',
                'tours'       => 'tour',
                'combos'      => 'combo',
                'partners'    => 'mang-luoi',
                'specialties' => 'chuyen-khoa',
            ],
            'en' => [
                'services'    => 'dich-vu',
                'tours'       => 'tour',
                'combos'      => 'combo',
                'partners'    => 'mang-luoi',
                'specialties' => 'specialties',
            ],
        ];

        foreach ($locales as $locale) {
            $slugs = $slugMap[$locale] ?? $slugMap['vi'];

            Route::middleware(['web', 'vmta.locale'])
                ->prefix($locale)
                ->name("site.{$locale}.catalog.")
                ->group(function () use ($slugs) {
                    require __DIR__.'/../../routes/public.php';
                });
        }
    }

    /**
     * Two top-level entries:
     *  - "Danh mục" group bundles the 3 customer-facing entities (Service / Tour / Combo).
     *  - "Mạng lưới" group bundles the organisational entities (Partner / Specialty).
     * Permission `catalog.index` matches the admin route middleware (see `routes/admin.php`).
     */
    protected function registerSidebarItems(): void
    {
        $sidebar = app(SidebarService::class);

        // "Danh mục" group (Dịch vụ / Tour / Combo) — sidebar entry hidden temporarily.
        // Routes (admin.services.*, admin.tours.*, admin.combos.*) + controllers remain
        // intact so existing data stays reachable via direct URL. Re-enable by
        // uncommenting the registerItem() call below.
        $catalogGroup = new SidebarItem(
            name: 'Danh mục',
            route: '',
            icon: '',
            permission: 'catalog.index',
            priority: 40,
            materialIcon: 'inventory_2',
            slug: 'catalog',
        );
        $catalogGroup->addChild(new SidebarItem('Dịch vụ', 'admin.services.index', '', 'catalog.index', 41, 'admin.services.*', 'medical_services', 'services'));
        $catalogGroup->addChild(new SidebarItem('Tour', 'admin.tours.index', '', 'catalog.index', 42, 'admin.tours.*', 'flight_takeoff', 'tours'));
        $catalogGroup->addChild(new SidebarItem('Combo', 'admin.combos.index', '', 'catalog.index', 43, 'admin.combos.*', 'redeem', 'combos'));
        // $sidebar->registerItem($catalogGroup);

        $networkGroup = new SidebarItem(
            name: 'Mạng lưới',
            route: '',
            icon: '',
            permission: 'catalog.index',
            priority: 50,
            materialIcon: 'hub',
            slug: 'network',
        );
        $networkGroup->addChild(new SidebarItem('Đối tác', 'admin.partners.index', '', 'catalog.index', 51, 'admin.partners.*', 'handshake', 'partners'));
        $networkGroup->addChild(new SidebarItem('Chuyên khoa', 'admin.specialties.index', '', 'catalog.index', 52, 'admin.specialties.*', 'stethoscope', 'specialties'));
        $networkGroup->addChild(new SidebarItem('Lead chuyên khoa', 'admin.specialty_leads.index', '', 'catalog.index', 54, 'admin.specialty_leads.*', 'forward_to_inbox', 'specialty-leads'));
        $sidebar->registerItem($networkGroup);
    }

    private function registerMorphMap(): void
    {
        // Stable aliases so renaming class FQCN doesn't break stored morph references.
        Relation::morphMap([
            'specialty' => Specialty::class,
            'destination' => Destination::class,
            'partner' => Partner::class,
            'service' => Service::class,
            'tour_package' => TourPackage::class,
            'combo' => Combo::class,
        ]);
    }

    private function bindRepositories(): void
    {
        $bindings = [
            SpecialtyRepositoryInterface::class => SpecialtyRepository::class,
            DestinationRepositoryInterface::class => DestinationRepository::class,
            PartnerRepositoryInterface::class => PartnerRepository::class,
            ServiceRepositoryInterface::class => ServiceRepository::class,
            TourPackageRepositoryInterface::class => TourPackageRepository::class,
            ComboRepositoryInterface::class => ComboRepository::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    private function bindServices(): void
    {
        $this->app->singleton(CatalogService::class);
        $this->app->singleton(SearchService::class);
        $this->app->singleton(FilterService::class);

        $this->app->singleton(BrowserSnapshotRunner::class);
        $this->app->singleton(LlmExtractor::class);
        $this->app->singleton(MediaImporter::class);
        $this->app->singleton(SpecialtyImporter::class);
    }
}
