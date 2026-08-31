<?php

namespace Packages\Content\Src\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Packages\Content\Src\Http\Controllers\Public\SitemapController;
use Packages\Content\Src\Models\Category;
use Packages\Content\Src\Models\AboutSection;
use Packages\Content\Src\Models\AllianceSection;
use Packages\Content\Src\Models\HomeSection;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Models\Post;
use Packages\Content\Src\Repositories\Eloquent\CategoryRepository;
use Packages\Content\Src\Repositories\Eloquent\AboutSectionRepository;
use Packages\Content\Src\Repositories\Eloquent\AchievementSectionRepository;
use Packages\Content\Src\Repositories\Eloquent\AllianceSectionRepository;
use Packages\Content\Src\Repositories\Eloquent\HomeSectionRepository;
use Packages\Content\Src\Repositories\Eloquent\MedicalCaseRepository;
use Packages\Content\Src\Repositories\Eloquent\MenuItemRepository;
use Packages\Content\Src\Repositories\Eloquent\MenuRepository;
use Packages\Content\Src\Repositories\Eloquent\PageRepository;
use Packages\Content\Src\Repositories\Eloquent\PostRepository;
use Packages\Content\Src\Repositories\Interfaces\CategoryRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\AboutSectionRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\AchievementSectionRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\AllianceSectionRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\HomeSectionRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\MedicalCaseRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\MenuItemRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\MenuRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\PageRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;
use Packages\Content\Src\Services\ContentService;
use Packages\Content\Src\Services\AboutPageService;
use Packages\Content\Src\Services\AchievementPageService;
use Packages\Content\Src\Services\AlliancePageService;
use Packages\Content\Src\Services\HomePageService;
use Packages\Content\Src\Services\MenuService;
use Packages\Content\Src\Services\RecentlyViewedService;
use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../configs/content.php', 'content');

        $this->bindRepositories();
        $this->bindServices();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'content');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'content');
        $this->registerMorphMap();
        $this->registerAdminRoutes();
        $this->registerPublicRoutes();
        $this->registerSitemapRoute();
        $this->registerSidebarItems();
        $this->registerPageviewMiddleware();
    }

    /**
     * Push CountPageview onto the global `web` middleware stack. The middleware
     * itself filters admin/api paths, so a single registration covers all public
     * GET traffic without touching individual route definitions.
     */
    protected function registerPageviewMiddleware(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', \Packages\Content\Src\Http\Middleware\CountPageview::class);
    }

    /**
     * Adds a "Content" parent group with 4 nested entries (Pages, Posts, Categories, Tags)
     * plus standalone Menus. Permission flag `content.index` is shared
     * (same coarse permission as the admin route group — see `permissions.php`).
     */
    protected function registerSidebarItems(): void
    {
        $sidebar = app(SidebarService::class);

        $contentGroup = new SidebarItem(
            name: 'Nội dung',
            route: '',
            icon: '',
            permission: 'content.index',
            priority: 20,
            materialIcon: 'article',
            slug: 'content',
        );
        $contentGroup->addChild(new SidebarItem('Trang', 'admin.pages.index', '', 'content.index', 21, 'admin.pages.*', 'description', 'pages'));
        $contentGroup->addChild(new SidebarItem('Bài viết', 'admin.posts.index', '', 'content.index', 22, 'admin.posts.*', 'feed', 'posts'));
        $contentGroup->addChild(new SidebarItem('Danh mục', 'admin.categories.index', '', 'content.index', 23, 'admin.categories.*', 'folder', 'categories'));
        $sidebar->registerItem($contentGroup);

        $sidebar->registerItem(new SidebarItem('Menu điều hướng', 'admin.menus.index', '', 'content.index', 30, 'admin.menus.*', 'menu', 'menus'));
        $sidebar->registerItem(new SidebarItem('Trang chủ', 'admin.home.index', '', 'home.manage', 25, 'admin.home.*', 'home', 'home'));
        $sidebar->registerItem(new SidebarItem('Giới thiệu', 'admin.about.index', '', 'about.manage', 26, 'admin.about.*', 'info', 'about'));
        $sidebar->registerItem(new SidebarItem('Giá trị cốt lõi', 'admin.core-values.index', '', 'about.manage', 29, 'admin.core-values.*', 'diamond', 'core-values'));
        $sidebar->registerItem(new SidebarItem('Mạng lưới Liên minh', 'admin.alliance.index', '', 'alliance.manage', 27, 'admin.alliance.*', 'hub', 'alliance'));
        $sidebar->registerItem(new SidebarItem('Thành tựu Y khoa', 'admin.achievement.index', '', 'achievement.manage', 28, 'admin.achievement.*', 'medical_services', 'achievement'));
    }

    /**
     * /sitemap.xml lives at root (no locale prefix) per Google's convention.
     * Returns a single multi-locale sitemap with hreflang alternates per URL.
     */
    private function registerSitemapRoute(): void
    {
        Route::middleware('web')
            ->get('/sitemap.xml', SitemapController::class)
            ->name('sitemap.xml');
    }

    /**
     * Mount public routes per locale, mirroring the per-locale name namespacing used by
     * SiteServiceProvider so Content routes can shadow the placeholder home without collisions.
     * Names: site.{locale}.content.{home|posts.*|page.show}
     */
    private function registerPublicRoutes(): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => []]));

        // Locale-aware URL slug map. Add new entries here when adding new locale-specific URLs.
        // Reserved slugs (per locale) are auto-derived from this map for the page catch-all regex.
        $slugMap = [
            'vi' => [
                'alliance'  => 'mang-luoi-lien-minh',
                'about'     => 'gioi-thieu',
                'medical'   => 'thanh-tuu-y-khoa',
                'heartLung' => 'ghep-dong-thoi-tim-phoi',
                'posts'     => 'tin-tuc',
            ],
            'en' => [
                'alliance'  => 'alliance-network',
                'about'     => 'about',
                'medical'   => 'medical-achievements',
                'heartLung' => 'simultaneous-heart-lung-transplant',
                'posts'     => 'news',
            ],
        ];

        foreach ($locales as $locale) {
            $slugs = $slugMap[$locale] ?? $slugMap['en'];

            Route::middleware(['web', 'vmta.locale'])
                ->prefix($locale)
                ->name("site.{$locale}.content.")
                ->group(function () use ($slugs) {
                    Route::get($slugs['alliance'], \Packages\Content\Src\Http\Controllers\Public\AllianceController::class)
                        ->name('alliance');

                    require __DIR__.'/../../routes/public.php';
                });
        }
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

    private function registerMorphMap(): void
    {
        // Stable alias for MenuItem.target_type so renaming class FQCN
        // does not break menu links pointing to Post/Page.
        Relation::morphMap([
            'page' => Page::class,
            'post' => Post::class,
            'category' => Category::class,
            'home_section' => HomeSection::class,
        ]);
    }

    private function bindRepositories(): void
    {
        $bindings = [
            PageRepositoryInterface::class => PageRepository::class,
            PostRepositoryInterface::class => PostRepository::class,
            CategoryRepositoryInterface::class => CategoryRepository::class,
            MenuRepositoryInterface::class => MenuRepository::class,
            MenuItemRepositoryInterface::class => MenuItemRepository::class,
            AboutSectionRepositoryInterface::class => AboutSectionRepository::class,
            AchievementSectionRepositoryInterface::class => AchievementSectionRepository::class,
            AllianceSectionRepositoryInterface::class => AllianceSectionRepository::class,
            HomeSectionRepositoryInterface::class => HomeSectionRepository::class,
            MedicalCaseRepositoryInterface::class => MedicalCaseRepository::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    private function bindServices(): void
    {
        $this->app->singleton(ContentService::class);
        $this->app->singleton(MenuService::class);
        $this->app->singleton(AboutPageService::class);
        $this->app->singleton(AchievementPageService::class);
        $this->app->singleton(AlliancePageService::class);
        $this->app->singleton(HomePageService::class);
        // Per-request lifecycle (reads/writes request-scoped cookie); singleton is fine since
        // it pulls Request from container each call.
        $this->app->singleton(RecentlyViewedService::class);
    }
}
