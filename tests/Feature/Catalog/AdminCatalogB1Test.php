<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Catalog\Database\Seeders\DestinationSeeder;
use Packages\Catalog\Database\Seeders\SpecialtySeeder;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class AdminCatalogB1Test extends TestCase
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

    /* ───────── Specialty ───────── */

    public function test_specialty_store_creates_with_translations(): void
    {
        $this->actingAs($this->admin())->post('/admin/specialties', [
            'icon' => 'tim-mach',
            'sort_order' => 1,
            'is_active' => '1',
            'translations' => [
                ['locale' => 'vi', 'name' => 'Tim mạch', 'slug' => 'tim-mach', 'description' => 'Mô tả'],
                ['locale' => 'en', 'name' => 'Cardiology', 'slug' => 'cardiology', 'description' => 'About'],
            ],
        ])->assertRedirect('/admin/specialties');

        $sp = Specialty::with('translations')->first();
        $this->assertSame('Tim mạch', $sp->translate('vi')->name);
        $this->assertSame('Cardiology', $sp->translate('en')->name);
    }

    public function test_specialty_slug_unique_per_locale(): void
    {
        $existing = Specialty::create(['sort_order' => 1, 'is_active' => true]);
        $existing->translateOrNew('vi')->fill(['name' => 'A', 'slug' => 'shared'])->save();

        $r = $this->actingAs($this->admin())
            ->from('/admin/specialties/create')
            ->post('/admin/specialties', [
                'translations' => [['locale' => 'vi', 'name' => 'B', 'slug' => 'shared']],
            ]);

        $r->assertSessionHasErrors(['translations.0.slug']);
        $this->assertSame(1, Specialty::count());
    }

    /* ───────── Partner ───────── */

    public function test_partner_store_with_destination_and_specialties(): void
    {
        $dest = Destination::create(['country_code' => 'VN', 'sort_order' => 1, 'is_active' => true]);
        $dest->translateOrNew('vi')->fill(['name' => 'HN', 'slug' => 'hn'])->save();

        $sp1 = Specialty::create(['sort_order' => 1, 'is_active' => true]);
        $sp1->translateOrNew('vi')->fill(['name' => 'Tim', 'slug' => 'tim'])->save();
        $sp2 = Specialty::create(['sort_order' => 2, 'is_active' => true]);
        $sp2->translateOrNew('vi')->fill(['name' => 'Nha', 'slug' => 'nha'])->save();

        $this->actingAs($this->admin())->post('/admin/partners', [
            'type' => Partner::TYPE_HOSPITAL,
            'destination_id' => $dest->id,
            'website' => 'https://example.com',
            'phone' => '+84 24 1234 5678',
            'email' => 'contact@example.com',
            'is_active' => '1',
            'sort_order' => 1,
            'specialty_ids' => [$sp1->id, $sp2->id],
            'translations' => [
                ['locale' => 'vi', 'name' => 'BV Test', 'slug' => 'bv-test', 'short_description' => 'Bệnh viện'],
            ],
        ])->assertRedirect('/admin/partners');

        $p = Partner::with(['specialties'])->first();
        $this->assertSame(Partner::TYPE_HOSPITAL, $p->type);
        $this->assertSame($dest->id, $p->destination_id);
        $this->assertSame(2, $p->specialties()->count());
    }

    public function test_partner_rejects_invalid_type(): void
    {
        $r = $this->actingAs($this->admin())
            ->from('/admin/partners/create')
            ->post('/admin/partners', [
                'type' => 'CASINO',
                'translations' => [['locale' => 'vi', 'name' => 'X', 'slug' => 'x']],
            ]);

        $r->assertSessionHasErrors(['type']);
    }

    public function test_partner_update_resyncs_specialty_pivot(): void
    {
        $sp1 = Specialty::create(['sort_order' => 1, 'is_active' => true]);
        $sp1->translateOrNew('vi')->fill(['name' => 'A', 'slug' => 'a'])->save();
        $sp2 = Specialty::create(['sort_order' => 2, 'is_active' => true]);
        $sp2->translateOrNew('vi')->fill(['name' => 'B', 'slug' => 'b'])->save();

        $p = Partner::create(['type' => Partner::TYPE_HOSPITAL, 'is_active' => true, 'sort_order' => 1]);
        $p->translateOrNew('vi')->fill(['name' => 'BV', 'slug' => 'bv'])->save();
        $p->specialties()->attach($sp1->id);

        $this->actingAs($this->admin())->put("/admin/partners/{$p->id}", [
            'type' => Partner::TYPE_HOSPITAL,
            'specialty_ids' => [$sp2->id],
            'translations' => [['locale' => 'vi', 'name' => 'BV', 'slug' => 'bv']],
        ])->assertRedirect();

        $this->assertSame([$sp2->id], $p->fresh()->specialties->pluck('id')->all());
    }

    public function test_partner_filter_by_type_and_destination(): void
    {
        $dest = Destination::create(['country_code' => 'VN', 'sort_order' => 1, 'is_active' => true]);
        $dest->translateOrNew('vi')->fill(['name' => 'HN', 'slug' => 'hn'])->save();

        $hospital = Partner::create(['type' => Partner::TYPE_HOSPITAL, 'destination_id' => $dest->id, 'is_active' => true, 'sort_order' => 1]);
        $hospital->translateOrNew('vi')->fill(['name' => 'BV1', 'slug' => 'bv1'])->save();

        $hotel = Partner::create(['type' => Partner::TYPE_HOTEL, 'is_active' => true, 'sort_order' => 2]);
        $hotel->translateOrNew('vi')->fill(['name' => 'Hotel1', 'slug' => 'hotel1'])->save();

        $r = $this->actingAs($this->admin())->get('/admin/partners?type=hospital');
        $r->assertOk();
        $r->assertSee('BV1');
        $r->assertDontSee('Hotel1');
    }

    /* ───────── Seeders ───────── */

    public function test_specialty_seeder_idempotent(): void
    {
        $this->seed(SpecialtySeeder::class);
        $countAfterFirst = Specialty::count();
        $this->seed(SpecialtySeeder::class);
        $this->assertSame($countAfterFirst, Specialty::count(), 'Re-seeding must not duplicate');
        $this->assertGreaterThanOrEqual(8, $countAfterFirst);
    }

    public function test_destination_seeder_creates_vn_cities(): void
    {
        $this->seed(DestinationSeeder::class);
        $haNoi = Destination::whereHas('translations', fn ($q) => $q->where('locale', 'vi')->where('slug', 'ha-noi'))->first();

        $this->assertNotNull($haNoi);
        $this->assertSame('VN', $haNoi->country_code);
    }

    /* ───────── Guards ───────── */

    public function test_guest_redirected_to_login_from_catalog_admin(): void
    {
        foreach (['/admin/specialties', '/admin/partners'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
