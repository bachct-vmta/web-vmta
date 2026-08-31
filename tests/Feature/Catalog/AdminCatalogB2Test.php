<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Catalog\Src\Models\Combo;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Models\Service;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\TourPackage;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\MediaFile;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

/**
 * Slice B2 — admin CRUD for Service / TourPackage / Combo.
 *
 * Covers status workflow, pivot resync, gallery JSON normalisation, currency upper-casing,
 * combo "non-empty bundle" rule, and shared per-locale slug validation.
 */
class AdminCatalogB2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@nguyenkhoi.dev')->first();
    }

    private function makeSpecialty(string $name, string $slug): Specialty
    {
        $sp = Specialty::create(['sort_order' => 1, 'is_active' => true]);
        $sp->translateOrNew('vi')->fill(['name' => $name, 'slug' => $slug])->save();

        return $sp;
    }

    private function makeDestination(string $name, string $slug): Destination
    {
        $d = Destination::create(['country_code' => 'VN', 'sort_order' => 1, 'is_active' => true]);
        $d->translateOrNew('vi')->fill(['name' => $name, 'slug' => $slug])->save();

        return $d;
    }

    private function makePartner(): Partner
    {
        $p = Partner::create(['type' => Partner::TYPE_HOSPITAL, 'is_active' => true, 'sort_order' => 1]);
        $p->translateOrNew('vi')->fill(['name' => 'BV', 'slug' => 'bv'])->save();

        return $p;
    }

    /* ───────── Service ───────── */

    public function test_service_store_with_specialties_and_destinations(): void
    {
        $partner = $this->makePartner();
        $sp = $this->makeSpecialty('Tim', 'tim');
        $dest = $this->makeDestination('HN', 'hn');
        // Seed gallery medias so `gallery_media_ids.*` exists rule passes.
        $m1 = MediaFile::create(['name' => 'a.jpg', 'alt' => 'a', 'permalink' => '/m/a.jpg', 'mine_type' => 'image/jpeg']);
        $m2 = MediaFile::create(['name' => 'b.jpg', 'alt' => 'b', 'permalink' => '/m/b.jpg', 'mine_type' => 'image/jpeg']);

        $this->actingAs($this->admin())->post('/admin/services', [
            'status' => 'published',
            'partner_id' => $partner->id,
            'published_at' => '2026-05-19T10:00',
            'price_from' => 1500000,
            'currency' => 'vnd',
            'sort_order' => 0,
            'gallery_media_ids' => "{$m1->id},{$m2->id}",
            'is_featured' => '1',
            'specialty_ids' => [$sp->id],
            'destination_ids' => [$dest->id],
            'translations' => [
                ['locale' => 'vi', 'title' => 'Gói khám', 'slug' => 'goi-kham', 'excerpt' => 'X'],
                ['locale' => 'en', 'title' => 'Health Check', 'slug' => 'health-check'],
            ],
        ])->assertRedirect('/admin/services');

        $svc = Service::with(['translations', 'specialties', 'destinations'])->first();
        $this->assertSame('VND', $svc->currency);
        $this->assertSame([$m1->id, $m2->id], $svc->gallery_media_ids);
        $this->assertTrue((bool) $svc->is_featured);
        $this->assertSame('published', $svc->status);
        $this->assertSame('Gói khám', $svc->translate('vi')->title);
        $this->assertSame([$sp->id], $svc->specialties->pluck('id')->all());
        $this->assertSame([$dest->id], $svc->destinations->pluck('id')->all());
    }

    public function test_service_published_requires_published_at(): void
    {
        $r = $this->actingAs($this->admin())
            ->from('/admin/services/create')
            ->post('/admin/services', [
                'status' => 'published',
                'translations' => [['locale' => 'vi', 'title' => 'X', 'slug' => 'x']],
            ]);

        $r->assertSessionHasErrors(['published_at']);
    }

    public function test_service_slug_unique_per_locale(): void
    {
        $svc = Service::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $svc->translateOrNew('vi')->fill(['title' => 'A', 'slug' => 'shared'])->save();

        $r = $this->actingAs($this->admin())
            ->from('/admin/services/create')
            ->post('/admin/services', [
                'status' => 'draft',
                'translations' => [['locale' => 'vi', 'title' => 'B', 'slug' => 'shared']],
            ]);

        $r->assertSessionHasErrors(['translations.0.slug']);
        $this->assertSame(1, Service::count());
    }

    public function test_service_rejects_unknown_gallery_media_id(): void
    {
        $r = $this->actingAs($this->admin())
            ->from('/admin/services/create')
            ->post('/admin/services', [
                'status' => 'draft',
                'gallery_media_ids' => '999999,888888',
                'translations' => [['locale' => 'vi', 'title' => 'X', 'slug' => 'x']],
            ]);

        $r->assertSessionHasErrors(['gallery_media_ids.0', 'gallery_media_ids.1']);
    }

    public function test_service_update_resyncs_pivots(): void
    {
        $sp1 = $this->makeSpecialty('A', 'a');
        $sp2 = $this->makeSpecialty('B', 'b');
        $svc = Service::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $svc->translateOrNew('vi')->fill(['title' => 'T', 'slug' => 't'])->save();
        $svc->specialties()->attach($sp1->id);

        $this->actingAs($this->admin())->put("/admin/services/{$svc->id}", [
            'status' => 'draft',
            'specialty_ids' => [$sp2->id],
            'translations' => [['locale' => 'vi', 'title' => 'T', 'slug' => 't']],
        ])->assertRedirect();

        $this->assertSame([$sp2->id], $svc->fresh()->specialties->pluck('id')->all());
    }

    /* ───────── TourPackage ───────── */

    public function test_tour_store_with_itinerary(): void
    {
        $dest = $this->makeDestination('DN', 'da-nang');

        $this->actingAs($this->admin())->post('/admin/tours', [
            'status' => 'draft',
            'duration_days' => 5,
            'price_from' => 9990000,
            'currency' => 'VND',
            'sort_order' => 0,
            'destination_ids' => [$dest->id],
            'translations' => [
                ['locale' => 'vi', 'title' => 'Tour Đà Nẵng', 'slug' => 'tour-da-nang', 'itinerary' => "Ngày 1: Sân bay\nNgày 2: Bãi biển"],
            ],
        ])->assertRedirect('/admin/tours');

        $tp = TourPackage::with(['translations', 'destinations'])->first();
        $this->assertSame(5, $tp->duration_days);
        $this->assertStringContainsString('Sân bay', $tp->translate('vi')->itinerary);
        $this->assertSame([$dest->id], $tp->destinations->pluck('id')->all());
    }

    public function test_tour_rejects_invalid_duration(): void
    {
        $r = $this->actingAs($this->admin())
            ->from('/admin/tours/create')
            ->post('/admin/tours', [
                'status' => 'draft',
                'duration_days' => 999,
                'translations' => [['locale' => 'vi', 'title' => 'X', 'slug' => 'x']],
            ]);

        $r->assertSessionHasErrors(['duration_days']);
    }

    public function test_tour_filter_by_status(): void
    {
        $a = TourPackage::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()]);
        $a->translateOrNew('vi')->fill(['title' => 'PublishedTour', 'slug' => 'pub'])->save();
        $b = TourPackage::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $b->translateOrNew('vi')->fill(['title' => 'DraftTour', 'slug' => 'drf'])->save();

        $r = $this->actingAs($this->admin())->get('/admin/tours?status=published');
        $r->assertOk();
        $r->assertSee('PublishedTour');
        $r->assertDontSee('DraftTour');
    }

    /* ───────── Combo ───────── */

    public function test_combo_requires_at_least_one_item(): void
    {
        $r = $this->actingAs($this->admin())
            ->from('/admin/combos/create')
            ->post('/admin/combos', [
                'status' => 'draft',
                'currency' => 'VND',
                'translations' => [['locale' => 'vi', 'title' => 'Empty', 'slug' => 'empty']],
            ]);

        $r->assertSessionHasErrors(['service_ids']);
        $this->assertSame(0, Combo::count());
    }

    public function test_combo_store_with_services_and_tours(): void
    {
        $svc = Service::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $svc->translateOrNew('vi')->fill(['title' => 'SVC', 'slug' => 'svc'])->save();

        $tour = TourPackage::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $tour->translateOrNew('vi')->fill(['title' => 'TR', 'slug' => 'tr'])->save();

        $this->actingAs($this->admin())->post('/admin/combos', [
            'status' => 'draft',
            'currency' => 'VND',
            'discount_percent' => 15,
            'service_ids' => [$svc->id],
            'tour_package_ids' => [$tour->id],
            'translations' => [
                ['locale' => 'vi', 'title' => 'Combo VN', 'slug' => 'combo-vn'],
            ],
        ])->assertRedirect('/admin/combos');

        $combo = Combo::with(['services', 'tourPackages'])->first();
        $this->assertEqualsWithDelta(15.0, (float) $combo->discount_percent, 0.001);
        $this->assertSame([$svc->id], $combo->services->pluck('id')->all());
        $this->assertSame([$tour->id], $combo->tourPackages->pluck('id')->all());
    }

    public function test_combo_rejects_discount_over_100(): void
    {
        $svc = Service::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $svc->translateOrNew('vi')->fill(['title' => 'S', 'slug' => 's'])->save();

        $r = $this->actingAs($this->admin())
            ->from('/admin/combos/create')
            ->post('/admin/combos', [
                'status' => 'draft',
                'discount_percent' => 150,
                'service_ids' => [$svc->id],
                'translations' => [['locale' => 'vi', 'title' => 'X', 'slug' => 'x']],
            ]);

        $r->assertSessionHasErrors(['discount_percent']);
    }

    /* ───────── Guards ───────── */

    public function test_guest_redirected_to_login_from_b2_admin(): void
    {
        foreach (['/admin/services', '/admin/tours', '/admin/combos'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
