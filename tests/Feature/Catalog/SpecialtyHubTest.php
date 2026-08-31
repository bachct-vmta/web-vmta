<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Catalog\Src\Models\Specialty;
use Tests\TestCase;

class SpecialtyHubTest extends TestCase
{
    use RefreshDatabase;

    private function specialty(string $name, string $slug, bool $active = true, int $sort = 1): Specialty
    {
        $sp = Specialty::create(['sort_order' => $sort, 'is_active' => $active, 'show_lead_form' => true]);
        $sp->translateOrNew('vi')->fill(['name' => $name, 'slug' => $slug])->save();
        $sp->translateOrNew('en')->fill(['name' => $name, 'slug' => $slug])->save();
        return $sp;
    }

    public function test_hub_renders_active_specialties(): void
    {
        $this->specialty('Nha khoa', 'nha-khoa', sort: 2);
        $this->specialty('Phụ sản', 'phu-san');

        $r = $this->get('/vi/chuyen-khoa');
        $r->assertOk();
        $r->assertSee('Nha khoa');
        $r->assertSee('Phụ sản');
        $r->assertSeeInOrder(['Phụ sản', 'Nha khoa']);
    }

    public function test_hub_excludes_inactive_specialties(): void
    {
        $this->specialty('Visible', 'visible');
        $this->specialty('Hidden', 'hidden', active: false);

        $r = $this->get('/vi/chuyen-khoa');
        $r->assertOk();
        $r->assertSee('Visible');
        $r->assertDontSee('>Hidden<', false);
    }

    public function test_hub_renders_seo_meta(): void
    {
        $this->specialty('Nha khoa', 'nha-khoa');

        $r = $this->get('/vi/chuyen-khoa');
        $r->assertOk();
        $r->assertSee('Chuyên khoa', false);
        $r->assertSee('<meta name="description"', false);
    }

    public function test_en_locale_serves_hub(): void
    {
        $this->specialty('Dentistry', 'dentistry');

        $this->get('/en/chuyen-khoa')->assertOk();
    }
}
