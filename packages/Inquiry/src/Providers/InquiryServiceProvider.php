<?php

namespace Packages\Inquiry\Src\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Packages\Inquiry\Src\Http\Controllers\Public\ContactController;
use Packages\Inquiry\Src\Http\Controllers\Public\EmergencyController;
use Packages\Inquiry\Src\Http\Controllers\Public\PartnerController;
use Packages\Inquiry\Src\Http\Controllers\Public\QuickInquiryController;
use Spatie\Honeypot\ProtectAgainstSpam;
use Packages\Core\Src\Services\SidebarItem;
use Packages\Core\Src\Services\SidebarService;
use Packages\Inquiry\Src\Models\Inquiry;
use Packages\Inquiry\Src\Models\InquiryNote;
use Packages\Inquiry\Src\Repositories\Eloquent\InquiryRepository;
use Packages\Inquiry\Src\Repositories\Interfaces\InquiryRepositoryInterface;
use Packages\Inquiry\Src\Repositories\Eloquent\ContactSectionRepository;
use Packages\Inquiry\Src\Repositories\Interfaces\ContactSectionRepositoryInterface;
use Packages\Inquiry\Src\Services\InquiryService;

class InquiryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../configs/inquiry.php', 'inquiry');

        $this->app->bind(InquiryRepositoryInterface::class, InquiryRepository::class);
        $this->app->bind(ContactSectionRepositoryInterface::class, ContactSectionRepository::class);
        $this->app->singleton(InquiryService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'inquiry');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'inquiry');
        $this->registerMorphMap();
        $this->registerPublicRoutes();
        $this->registerAdminRoutes();
        $this->registerSidebarItems();

        $this->publishes([
            __DIR__.'/../../configs/inquiry.php' => config_path('inquiry.php'),
        ], 'inquiry-config');
    }

    /**
     * Inquiry (Leads) sits high in the sidebar — sales workflow priority.
     */
    protected function registerSidebarItems(): void
    {
        $sidebar = app(SidebarService::class);

        $sidebar->registerItem(new SidebarItem(
            name: 'Yêu cầu liên hệ',
            route: 'admin.inquiries.index',
            icon: '',
            permission: 'inquiry.index',
            priority: 60,
            activeRoutePattern: 'admin.inquiries.*',
            materialIcon: 'inbox',
            slug: 'inquiries',
        ));

        $sidebar->registerItem(new SidebarItem(
            name: 'Trang Liên hệ',
            route: 'admin.contact.index',
            icon: '',
            permission: 'contact.manage',
            priority: 61,
            activeRoutePattern: 'admin.contact.*',
            materialIcon: 'contact_mail',
            slug: 'contact-page',
        ));
    }

    /**
     * Mount admin routes inside Core's admin group (auth + permission middleware).
     * Names are prefixed by the configured admin_route_name() so URLs follow the same
     * env-driven prefix as Core.
     */
    private function registerAdminRoutes(): void
    {
        Route::prefix(admin_prefix())
            ->middleware(['web', 'auth', 'permission'])
            ->name(admin_route_name())
            ->group(__DIR__.'/../../routes/admin.php');
    }

    /**
     * Mount locale-aware contact routes before Content's {slug} catch-all.
     * vi → /vi/lien-he, en → /en/contact (matches vmta.test URL convention).
     * Inline registration (not file include) so locale-specific slugs can be passed.
     */
    private function registerPublicRoutes(): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => []]));
        $throttle = 'throttle:'.config('inquiry.throttle', '5,1');

        $slugMap = ['vi' => ['contact' => 'lien-he', 'partner' => 'doi-tac']];

        foreach ($locales as $locale) {
            $contactSlug = $slugMap[$locale]['contact'] ?? 'contact';
            $partnerSlug = $slugMap[$locale]['partner'] ?? 'partner';

            Route::middleware(['web', 'vmta.locale'])
                ->prefix($locale)
                ->name("inquiry.{$locale}.")
                ->group(function () use ($contactSlug, $partnerSlug, $throttle) {
                    Route::get($contactSlug, [ContactController::class, 'show'])->name('contact.show');
                    // TODO: re-enable when ready (Phase N)
                    // Route::get('khan-cap', [EmergencyController::class, 'show'])->name('emergency.show');

                    Route::middleware([ProtectAgainstSpam::class, $throttle])->group(function () use ($contactSlug, $partnerSlug) {
                        Route::post($contactSlug, [ContactController::class, 'store'])->name('contact.store');
                        Route::post("{$contactSlug}/{$partnerSlug}", [PartnerController::class, 'store'])->name('partner.store');
                        // TODO: re-enable when ready (Phase N)
                        // Route::post('khan-cap', [EmergencyController::class, 'store'])->name('emergency.store');
                        Route::post('inquiry/quick', [QuickInquiryController::class, 'store'])->name('quick.store');
                    });
                });
        }
    }

    private function registerMorphMap(): void
    {
        // Stable aliases so morphed inquiry references survive class FQCN renames.
        Relation::morphMap([
            'inquiry' => Inquiry::class,
            'inquiry_note' => InquiryNote::class,
        ]);
    }
}
