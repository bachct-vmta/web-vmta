<?php

namespace Packages\Core\Src\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Packages\Core\Src\Console\Commands\MakePackageCommand;
use Packages\Core\Src\Console\Commands\MakeTableCommand;
use Packages\Core\Src\Console\Commands\MediaCleanupCommand;
use Packages\Core\Src\Http\Middleware\PermissionMiddleware;
use Packages\Core\Src\Models\Role;
use Packages\Core\Src\Models\User;
use Packages\Core\Src\Repositories\Eloquent\ActivityLogRepository;
use Packages\Core\Src\Repositories\Eloquent\GoogleDriveCredentialRepository;
use Packages\Core\Src\Repositories\Eloquent\MediaFileRepository;
use Packages\Core\Src\Repositories\Eloquent\MediaFolderRepository;
use Packages\Core\Src\Repositories\Eloquent\MediaSettingRepository;
use Packages\Core\Src\Repositories\Eloquent\RoleRepository;
use Packages\Core\Src\Repositories\Eloquent\SettingRepository;
use Packages\Core\Src\Repositories\Eloquent\UserRepository;
use Packages\Core\Src\Repositories\Interfaces\ActivityLogRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\GoogleDriveCredentialRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\MediaFileRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\MediaFolderRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\MediaSettingRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\RoleRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\SettingRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\UserRepositoryInterface;
use Packages\Core\Src\Services\PermissionService;
use Packages\Core\Src\Services\SettingService;
use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;
use Packages\Core\Src\Services\WidgetItem;
use Packages\Core\Src\Services\WidgetService;
use Packages\Core\Src\Traits\LoadAndPublishDataTrait;
use Packages\Core\Src\View\Components\Media\ActionFormModal;
use Packages\Core\Src\View\Components\Media\ButtonField;
use Packages\Core\Src\View\Components\Media\ConfirmModal;
use Packages\Core\Src\View\Components\Media\InputField;

class CoreServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected array $commands = [
        MakePackageCommand::class,
        MakeTableCommand::class,
        MediaCleanupCommand::class,
    ];

    public function register(): void
    {
        // Initialize namespace and basePath first
        $this->setNamespace('Core');

        // Register Database namespace (seeders) — Composer symlink may not auto-load this
        $basePath = $this->getBasePath();
        if (is_dir($basePath.'/database')) {
            $classLoader = require base_path('vendor/autoload.php');
            $classLoader->addPsr4('Packages\\Core\\Database\\', $basePath.'/database/');
        }

        // Register Core Repositories as singletons (bound to interfaces)
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->singleton(ActivityLogRepositoryInterface::class, ActivityLogRepository::class);

        // Register Core Services
        $this->app->singleton(PermissionService::class, fn () => new PermissionService);
        $this->app->singleton(SidebarService::class, fn () => new SidebarService);
        $this->app->singleton(WidgetService::class, fn () => new WidgetService);
        $this->app->singleton(SettingService::class);

        // Register aliases
        $this->app->alias(PermissionService::class, 'permission');
        $this->app->alias(SidebarService::class, 'sidebar');
        $this->app->alias(WidgetService::class, 'widget');

        // Register Media repositories
        $this->app->bind(MediaSettingRepositoryInterface::class, MediaSettingRepository::class);
        $this->app->bind(MediaFileRepositoryInterface::class, MediaFileRepository::class);
        $this->app->bind(MediaFolderRepositoryInterface::class, MediaFolderRepository::class);

        // Register Google Drive repository
        $this->app->bind(GoogleDriveCredentialRepositoryInterface::class, GoogleDriveCredentialRepository::class);

        // Load Core helper
        $coreHelperPath = $this->getBasePath().'/src/Helpers/core_helper.php';
        if (file_exists($coreHelperPath)) {
            File::requireOnce($coreHelperPath);
        }

        // Load Settings helper (global setting() function)
        $settingsHelperPath = $this->getBasePath().'/src/Helpers/settings.php';
        if (file_exists($settingsHelperPath)) {
            File::requireOnce($settingsHelperPath);
        }

        // Load Media helper
        $helperPath = $this->getBasePath().'/src/Helpers/media_helper.php';
        if (file_exists($helperPath)) {
            File::requireOnce($helperPath);
        }

        // Merge Core config
        $this->mergeConfigFrom($this->getBasePath().'/configs/core.php', 'core');

        // Merge Media config
        $this->mergeConfigFrom($this->getBasePath().'/configs/file-manager.php', 'file-manager');

        // Register console commands
        $this->commands($this->commands);
    }

    public function boot(): void
    {
        $this->loadMigrations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'admin', 'auth', 'media'])
            ->loadAndPublishTranslations();

        // Load Media views and translations
        $this->loadViewsFrom($this->getBasePath().'/resources/views/media', 'core-media');
        $this->loadTranslationsFrom($this->getBasePath().'/resources/lang', 'core-media');

        // Register Core permissions
        $permissions = require $this->getBasePath().'/configs/permissions.php';
        app(PermissionService::class)->registerPermissions($permissions);

        // Register middleware alias
        $this->app['router']->aliasMiddleware('permission', PermissionMiddleware::class);

        // Register directives, sidebar, widgets
        $this->registerBladeDirectives();
        $this->registerMediaComponents();
        $this->registerSidebarItems();
        $this->registerWidgets();

        // Schedule media cleanup
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('media:cleanup')->cron('*/5 * * * *');
        });

        // Apply mail config overrides from DB settings (after app booted so DB available).
        $this->app->booted(function () {
            $this->applyMailConfigFromSettings();
        });
    }

    /**
     * Override `config('mail.*')` with values from Settings (when configured).
     * Skipped silently if DB unavailable (fresh install, migrate context).
     */
    protected function applyMailConfigFromSettings(): void
    {
        try {
            $host = setting('mail.smtp_host');
            if (! empty($host)) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $host,
                    'mail.mailers.smtp.port' => (int) setting('mail.smtp_port', 587),
                    'mail.mailers.smtp.username' => setting('mail.smtp_username') ?: null,
                    'mail.mailers.smtp.password' => setting('mail.smtp_password') ?: null,
                    'mail.mailers.smtp.encryption' => setting('mail.smtp_encryption') ?: null,
                ]);
            }

            if (($fromAddress = setting('mail.from_address'))) {
                config(['mail.from.address' => $fromAddress]);
            }
            if (($fromName = setting('mail.from_name'))) {
                config(['mail.from.name' => $fromName]);
            }
        } catch (\Throwable) {
            // DB not ready (migrate fresh) — fall back to env-based config.
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('permission', fn (string $flag) => auth()->check() && auth()->user()->hasPermission($flag));
        Blade::if('anypermission', fn (array $flags) => auth()->check() && auth()->user()->hasAnyPermission($flags));
        Blade::if('superuser', fn () => auth()->check() && auth()->user()->isSuperUser());
        Blade::if('role', fn (string $slug) => auth()->check() && auth()->user()->role && auth()->user()->role->slug === $slug);
    }

    protected function registerMediaComponents(): void
    {
        Blade::component('core-media::confirm-modal', ConfirmModal::class);
        Blade::component('core-media::action-form-modal', ActionFormModal::class);
        Blade::component('core-media::input-field', InputField::class);
        Blade::component('core-media::button-field', ButtonField::class);
    }

    protected function registerSidebarItems(): void
    {
        $sidebar = app(SidebarService::class);

        $sidebar->registerItem(new SidebarItem(
            name: 'Dashboard',
            route: 'admin.dashboard',
            icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
            permission: null,
            priority: 0,
            activeRoutePattern: 'admin.dashboard',
            materialIcon: 'dashboard',
            slug: 'dashboard'
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Người dùng',
            route: 'admin.users.index',
            icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            permission: 'users.index',
            priority: 10,
            activeRoutePattern: 'admin.users.*',
            materialIcon: 'group',
            slug: 'users'
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Quản lý Media',
            route: 'admin.media.index',
            icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>',
            permission: 'media.index',
            priority: 15,
            activeRoutePattern: 'admin.media.index|admin.media.loadMedia',
            materialIcon: 'folder_open',
            slug: 'media'
        ));

        // Media Settings đặt trong section System (priority > 200)
        $sidebar->registerItem(new SidebarItem(
            name: 'Cài đặt Media',
            route: 'admin.media.settings.index',
            icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            permission: 'media.settings',
            priority: 201,
            activeRoutePattern: 'admin.media.settings.*',
            materialIcon: 'perm_media',
            slug: 'settings'  // Slug 'settings' để hiển thị trong section SYSTEM
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Cài đặt hệ thống',
            route: 'admin.settings.index',
            icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            permission: 'settings.index',
            priority: 210,
            activeRoutePattern: 'admin.settings.*',
            materialIcon: 'tune',
            slug: 'settings'
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Vai trò',
            route: 'admin.roles.index',
            icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            permission: 'roles.index',
            priority: 200,
            activeRoutePattern: 'admin.roles.*',
            materialIcon: 'shield_person',
            slug: 'roles'
        ));
    }

    protected function registerWidgets(): void
    {
        $widget = app(WidgetService::class);

        $widget->registerWidget(new WidgetItem(
            name: 'Tổng người dùng',
            value: fn () => User::count(),
            icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            bgColor: 'bg-blue-50 dark:bg-blue-900/20',
            iconColor: 'text-blue-600 dark:text-blue-400',
            permission: 'users.index',
            priority: 10,
            materialIcon: 'group'
        ));

        $widget->registerWidget(new WidgetItem(
            name: 'Vai trò',
            value: fn () => Role::count(),
            icon: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            bgColor: 'bg-purple-50 dark:bg-purple-900/20',
            iconColor: 'text-purple-600 dark:text-purple-400',
            permission: 'roles.index',
            priority: 20,
            materialIcon: 'shield_person'
        ));
    }
}
