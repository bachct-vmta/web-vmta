<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Catalog\Src\Models\Combo;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Models\Service;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\TourPackage;
use Tests\TestCase;

/**
 * Slice C1 — public list + detail surface for Service/Tour/Combo/Partner.
 *
 * Covers: URL conventions (/{locale}/dich-vu|tour|combo|mang-luoi), published-only filter,
 * locale-aware slug routing, Spatie filter pipeline, CTA cascade, 404 on draft/unknown slug.
 */
class PublicCatalogC1Test extends TestCase
{
    use RefreshDatabase;

    private function specialty(string $name, string $slug): Specialty
    {
        $sp = Specialty::create(['sort_order' => 1, 'is_active' => true]);
        $sp->translateOrNew('vi')->fill(['name' => $name, 'slug' => $slug])->save();
        $sp->translateOrNew('en')->fill(['name' => $name, 'slug' => $slug])->save();

        return $sp;
    }

    private function destination(string $name, string $slug): Destination
    {
        $d = Destination::create(['country_code' => 'VN', 'sort_order' => 1, 'is_active' => true]);
        $d->translateOrNew('vi')->fill(['name' => $name, 'slug' => $slug])->save();
        $d->translateOrNew('en')->fill(['name' => $name, 'slug' => $slug])->save();

        return $d;
    }

    private function partner(string $name, string $slug, ?Destination $dest = null, string $type = Partner::TYPE_HOSPITAL): Partner
    {
        $p = Partner::create([
            'type' => $type,
            'destination_id' => $dest?->id,
            'website' => 'https://example.com',
            'phone' => '+84 24 1234 5678',
            'email' => 'x@example.com',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $p->translateOrNew('vi')->fill(['name' => $name, 'slug' => $slug, 'short_description' => 'Bệnh viện'])->save();
        $p->translateOrNew('en')->fill(['name' => $name, 'slug' => $slug, 'short_description' => 'Hospital'])->save();

        return $p;
    }

    private function publishedService(string $title, string $slug): Service
    {
        $svc = Service::create([
            'status' => 'published',
            'currency' => 'VND',
            'is_featured' => false,
            'sort_order' => 0,
            'published_at' => now()->subDay(),
            'price_from' => 1500000,
        ]);
        $svc->translateOrNew('vi')->fill(['title' => $title, 'slug' => $slug, 'excerpt' => 'tóm tắt'])->save();

        return $svc;
    }

    /* ───────── Service ───────── */

    public function test_service_index_lists_only_published(): void
    {
        $pub = $this->publishedService('Gói khám tổng quát', 'goi-kham-tong-quat');
        $draft = Service::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $draft->translateOrNew('vi')->fill(['title' => 'Bản nháp', 'slug' => 'ban-nhap'])->save();

        $r = $this->get('/vi/dich-vu');
        $r->assertOk();
        $r->assertSee('Gói khám tổng quát');
        $r->assertDontSee('Bản nháp');
    }

    public function test_service_filter_by_specialty_slug(): void
    {
        $tim = $this->specialty('Tim mạch', 'tim-mach');
        $nha = $this->specialty('Nha khoa', 'nha-khoa');

        $a = $this->publishedService('Khám tim', 'kham-tim');
        $a->specialties()->attach($tim->id);
        $b = $this->publishedService('Khám răng', 'kham-rang');
        $b->specialties()->attach($nha->id);

        $r = $this->get('/vi/dich-vu?filter[specialty]=tim-mach');
        $r->assertOk();
        $r->assertSee('Khám tim');
        $r->assertDontSee('Khám răng');
    }

    public function test_service_show_returns_404_for_draft(): void
    {
        $draft = Service::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $draft->translateOrNew('vi')->fill(['title' => 'Nháp', 'slug' => 'nhap'])->save();

        $this->get('/vi/dich-vu/nhap')->assertNotFound();
    }

    public function test_service_show_renders_translation_and_cta(): void
    {
        $svc = $this->publishedService('Gói A', 'goi-a');
        $svc->update(['cta_app_url' => 'https://app.example/booking']);

        $r = $this->get('/vi/dich-vu/goi-a');
        $r->assertOk();
        $r->assertSee('Gói A');
        $r->assertSee('https://app.example/booking');
    }

    /* ───────── Tour ───────── */

    public function test_tour_index_published_only(): void
    {
        $pub = TourPackage::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay(), 'duration_days' => 5]);
        $pub->translateOrNew('vi')->fill(['title' => 'Tour Đà Nẵng', 'slug' => 'tour-da-nang'])->save();
        $draft = TourPackage::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $draft->translateOrNew('vi')->fill(['title' => 'TourDraft', 'slug' => 'tour-draft'])->save();

        $r = $this->get('/vi/tour');
        $r->assertOk();
        $r->assertSee('Tour Đà Nẵng');
        $r->assertDontSee('TourDraft');
    }

    public function test_tour_show_includes_itinerary(): void
    {
        $tp = TourPackage::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay(), 'duration_days' => 3]);
        $tp->translateOrNew('vi')->fill(['title' => 'Trekking', 'slug' => 'trekking', 'itinerary' => "Day 1: arrive\nDay 2: hike"])->save();

        $r = $this->get('/vi/tour/trekking');
        $r->assertOk();
        $r->assertSee('Day 1: arrive');
    }

    /* ───────── Combo ───────── */

    public function test_combo_index_shows_published(): void
    {
        $c = Combo::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay(), 'discount_percent' => 15]);
        $c->translateOrNew('vi')->fill(['title' => 'Combo VN', 'slug' => 'combo-vn'])->save();

        $r = $this->get('/vi/combo');
        $r->assertOk();
        $r->assertSee('Combo VN');
        $r->assertSee('15%');
    }

    public function test_combo_show_lists_bundled_items(): void
    {
        $svc = $this->publishedService('Khám tim', 'kham-tim-c');
        $tp = TourPackage::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay()]);
        $tp->translateOrNew('vi')->fill(['title' => 'Tour Hue', 'slug' => 'tour-hue'])->save();

        $c = Combo::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay()]);
        $c->translateOrNew('vi')->fill(['title' => 'Sức khoẻ + Du lịch', 'slug' => 'health-travel'])->save();
        $c->services()->attach($svc->id);
        $c->tourPackages()->attach($tp->id);

        $r = $this->get('/vi/combo/health-travel');
        $r->assertOk();
        $r->assertSee('Khám tim');
        $r->assertSee('Tour Hue');
    }

    public function test_combo_show_hides_draft_children(): void
    {
        $pubSvc = $this->publishedService('Pub Service', 'pub-svc');
        $draftSvc = Service::create(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]);
        $draftSvc->translateOrNew('vi')->fill(['title' => 'DraftLeak', 'slug' => 'draft-leak'])->save();

        $c = Combo::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay()]);
        $c->translateOrNew('vi')->fill(['title' => 'Mix', 'slug' => 'mix'])->save();
        $c->services()->attach([$pubSvc->id, $draftSvc->id]);

        $r = $this->get('/vi/combo/mix');
        $r->assertOk();
        $r->assertSee('Pub Service');
        $r->assertDontSee('DraftLeak');
    }

    /* ───────── Partner ───────── */

    public function test_partner_index_filter_by_type(): void
    {
        $hosp = $this->partner('BV A', 'bv-a', null, Partner::TYPE_HOSPITAL);
        $hotel = $this->partner('Hotel B', 'hotel-b', null, Partner::TYPE_HOTEL);

        $r = $this->get('/vi/mang-luoi?filter[type]=hospital');
        $r->assertOk();
        $r->assertSee('BV A');
        $r->assertDontSee('Hotel B');
    }

    public function test_partner_show_renders(): void
    {
        $dn = $this->destination('Đà Nẵng', 'da-nang');
        $this->partner('Vinmec Đà Nẵng', 'vinmec-da-nang', $dn);

        $r = $this->get('/vi/mang-luoi/vinmec-da-nang');
        $r->assertOk();
        $r->assertSee('Vinmec Đà Nẵng');
        $r->assertSee('Đà Nẵng');
    }

    public function test_partner_inactive_returns_404(): void
    {
        $p = Partner::create(['type' => Partner::TYPE_HOSPITAL, 'is_active' => false, 'sort_order' => 1]);
        $p->translateOrNew('vi')->fill(['name' => 'Hidden', 'slug' => 'hidden'])->save();

        $this->get('/vi/mang-luoi/hidden')->assertNotFound();
    }

    /* ───────── Locale routing ───────── */

    public function test_en_locale_serves_catalog_routes(): void
    {
        $svc = $this->publishedService('Pkg', 'pkg-vi');
        // Add EN slug so the detail route resolves under /en. Mirrors VI-slug-across-locales
        // policy: proper nouns keep VI form, generic English content gets its own slug.
        $svc->translateOrNew('en')->fill(['title' => 'Pkg EN', 'slug' => 'pkg-en'])->save();

        $this->get('/en/dich-vu')->assertOk();
        $this->get('/en/dich-vu/pkg-en')->assertOk();
        $this->get('/en/tour')->assertOk();
        $this->get('/en/combo')->assertOk();
        $this->get('/en/mang-luoi')->assertOk();
    }

    /* ───────── CTA fallback ───────── */

    public function test_cta_falls_back_to_config_when_entity_empty(): void
    {
        config(['catalog.cta_app_url_default' => 'https://app.vmta/default']);
        $svc = $this->publishedService('No-CTA', 'no-cta');

        $r = $this->get('/vi/dich-vu/no-cta');
        $r->assertOk();
        $r->assertSee('https://app.vmta/default');
    }

    public function test_cta_rejects_javascript_scheme_in_config_fallback(): void
    {
        config(['catalog.cta_app_url_default' => 'javascript:alert(1)']);
        $svc = $this->publishedService('No-CTA-2', 'no-cta-2');

        $r = $this->get('/vi/dich-vu/no-cta-2');
        $r->assertOk();
        // Scheme guard in CatalogService::resolveCta strips non-http(s) fallbacks.
        $r->assertDontSee('javascript:', false);
    }

    public function test_body_is_purified_against_xss(): void
    {
        $svc = $this->publishedService('Pure', 'pure');
        $svc->translations->first()->update(['body' => '<p>safe</p><script>alert("xss")</script>']);

        $r = $this->get('/vi/dich-vu/pure');
        $r->assertOk();
        $r->assertSee('safe', false);
        $r->assertDontSee('<script>', false);
    }

    /* ───────── Quick-inquiry partial embed (Phase 4 deferred → done) ───────── */

    public function test_service_show_embeds_quick_inquiry_with_source_ref(): void
    {
        $svc = $this->publishedService('Embed-Svc', 'embed-svc');

        $r = $this->get('/vi/dich-vu/embed-svc');
        $r->assertOk();
        $r->assertSee('name="source_ref_type" value="service"', false);
        $r->assertSee('name="source_ref_id" value="'.$svc->id.'"', false);
        $r->assertSee(route('inquiry.vi.quick.store'), false);
        // Submission-contract lock: CSRF + consent must render so the form actually submits.
        $r->assertSee('name="_token"', false);
        $r->assertSee('name="consent_given"', false);
    }

    public function test_tour_show_embeds_quick_inquiry_with_tour_package_ref(): void
    {
        $tp = TourPackage::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay()]);
        $tp->translateOrNew('vi')->fill(['title' => 'Embed-Tour', 'slug' => 'embed-tour'])->save();

        $r = $this->get('/vi/tour/embed-tour');
        $r->assertOk();
        $r->assertSee('name="source_ref_type" value="tour_package"', false);
        $r->assertSee('name="source_ref_id" value="'.$tp->id.'"', false);
    }

    public function test_combo_show_embeds_quick_inquiry_with_combo_ref(): void
    {
        $c = Combo::create(['status' => 'published', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0, 'published_at' => now()->subDay()]);
        $c->translateOrNew('vi')->fill(['title' => 'Embed-Combo', 'slug' => 'embed-combo'])->save();

        $r = $this->get('/vi/combo/embed-combo');
        $r->assertOk();
        $r->assertSee('name="source_ref_type" value="combo"', false);
        $r->assertSee('name="source_ref_id" value="'.$c->id.'"', false);
    }

    public function test_partner_show_does_not_embed_quick_inquiry(): void
    {
        // Lock the deliberate exclusion: QuickInquiryRequest allowlist is
        // ['service', 'tour_package', 'combo']. Partner detail must NOT embed the
        // partial — otherwise submissions silently 422 from validation.
        $dn = $this->destination('TP HCM', 'tp-hcm');
        $this->partner('BV Exclude', 'bv-exclude', $dn);

        $r = $this->get('/vi/mang-luoi/bv-exclude');
        $r->assertOk();
        $r->assertDontSee('name="source_ref_type"', false);
    }
}
