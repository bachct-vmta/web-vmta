<?php

namespace Packages\Chatbot\Src\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Packages\Chatbot\Src\Repositories\ChatbotSessionRepository;
use Packages\Chatbot\Src\Services\ChatbotSessionService;
use Packages\Chatbot\Src\Services\ChatbotStreamRelay;
use Packages\Chatbot\Src\Services\TourismApiClient;
use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;

class ChatbotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../configs/chatbot.php', 'chatbot');

        $this->app->singleton(TourismApiClient::class);
        $this->app->singleton(ChatbotSessionRepository::class);
        $this->app->singleton(ChatbotSessionService::class);
        $this->app->singleton(ChatbotStreamRelay::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'chatbot');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'chatbot');

        $this->registerPublicRoutes();
        $this->registerAdminRoutes();
        $this->registerSidebarItems();

        $this->publishes([
            __DIR__.'/../../configs/chatbot.php' => config_path('chatbot.php'),
        ], 'chatbot-config');
    }

    private function registerPublicRoutes(): void
    {
        Route::middleware(['web'])
            ->group(__DIR__.'/../../routes/web.php');
    }

    private function registerAdminRoutes(): void
    {
        Route::prefix(admin_prefix())
            ->middleware(['web', 'auth', 'permission'])
            ->name(admin_route_name())
            ->group(__DIR__.'/../../routes/admin.php');
    }

    private function registerSidebarItems(): void
    {
        $sidebar = app(SidebarService::class);

        // Parent group — rendered as collapsible menu, route unused.
        $sidebar->registerItem(new SidebarItem(
            name: 'Chatbot',
            route: '',
            icon: '',
            permission: 'chatbot.settings',
            priority: 55,
            activeRoutePattern: 'admin.chatbot.*',
            materialIcon: 'smart_toy',
            slug: 'chatbot',
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Cài đặt',
            route: 'admin.chatbot.settings.index',
            icon: '',
            permission: 'chatbot.settings',
            priority: 55,
            activeRoutePattern: 'admin.chatbot.settings.*',
            materialIcon: 'settings',
            slug: 'chatbot-settings',
            parentSlug: 'chatbot',
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Document groups',
            route: 'admin.chatbot.document-groups.index',
            icon: '',
            permission: 'chatbot.document_groups',
            priority: 56,
            activeRoutePattern: 'admin.chatbot.document-groups.*',
            materialIcon: 'folder_special',
            slug: 'chatbot-document-groups',
            parentSlug: 'chatbot',
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Documents',
            route: 'admin.chatbot.documents.index',
            icon: '',
            permission: 'chatbot.documents',
            priority: 57,
            activeRoutePattern: 'admin.chatbot.documents.*',
            materialIcon: 'description',
            slug: 'chatbot-documents',
            parentSlug: 'chatbot',
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Conversations',
            route: 'admin.chatbot.conversations.index',
            icon: '',
            permission: 'chatbot.conversations',
            priority: 58,
            activeRoutePattern: 'admin.chatbot.conversations.*',
            materialIcon: 'forum',
            slug: 'chatbot-conversations',
            parentSlug: 'chatbot',
        ));
    }
}
