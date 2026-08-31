<?php

namespace Tests\Feature\Dental;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Dental\Database\Seeders\DentalCategorySeeder;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Models\DentalFacility;
use Packages\Dental\Src\Models\DentalService;
use Tests\TestCase;

class DentalPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DentalCategorySeeder::class);
    }

    private function facility(string $slug = 'bv-a', string $status = 'published'): DentalFacility
    {
        $facility = DentalFacility::create([
            'dental_category_id' => DentalCategory::first()->id,
            'status' => $status,
            'published_at' => now(),
            'is_operating' => true,
        ]);
        $facility->translateOrNew('vi')->fill(['name' => 'Bệnh viện A', 'slug' => $slug, 'address' => '123 Nguyễn Văn Cừ']);
        $facility->translateOrNew('en')->fill(['name' => 'Hospital A', 'slug' => $slug.'-en']);
        $facility->save();

        return $facility;
    }

    private function service(DentalFacility $facility, string $slug = 'boc-rang-su', string $status = 'published'): DentalService
    {
        $service = DentalService::create([
            'dental_facility_id' => $facility->id,
            'status' => $status,
            'published_at' => now(),
        ]);
        $service->translateOrNew('vi')->fill([
            'title' => 'Bọc răng sứ',
            'slug' => $slug,
            'hero_h1' => 'Những điều bạn cần biết khi bọc răng sứ',
            'price_table_html' => '<table><tr><td>Răng sứ Titan</td><td>3.000.000</td></tr></table>',
        ]);
        $service->save();

        return $service;
    }

    public function test_directory_lists_published_facilities_grouped_by_category(): void
    {
        $this->facility();

        $this->get('/vi/kham-nha')
            ->assertOk()
            ->assertSee('Bệnh viện A')
            ->assertSee('Bệnh viện')       // tên danh mục
            ->assertSee('Đang hoạt động')
            ->assertSee('123 Nguyễn Văn Cừ');
    }

    public function test_directory_hides_draft_facilities(): void
    {
        $this->facility('bv-nhap', 'draft');

        $this->get('/vi/kham-nha')->assertOk()->assertDontSee('Bệnh viện A');
    }

    public function test_directory_works_in_english(): void
    {
        $this->facility();

        $this->get('/en/dental-care')->assertOk()->assertSee('Hospital A');
    }

    public function test_facility_page_lists_its_services(): void
    {
        $facility = $this->facility();
        $this->service($facility);

        $this->get('/vi/kham-nha/bv-a')
            ->assertOk()
            ->assertSee('Bệnh viện A')
            ->assertSee('Bọc răng sứ');
    }

    public function test_draft_facility_returns_404(): void
    {
        $this->facility('bv-nhap', 'draft');

        $this->get('/vi/kham-nha/bv-nhap')->assertNotFound();
    }

    public function test_service_page_uses_the_hero_heading_and_keeps_table_markup(): void
    {
        $facility = $this->facility();
        $this->service($facility);

        $response = $this->get('/vi/kham-nha/bv-a/boc-rang-su');

        $response->assertOk()
            ->assertSee('Những điều bạn cần biết khi bọc răng sứ')
            ->assertSee('<table', false)
            ->assertSee('Răng sứ Titan');
    }

    public function test_draft_service_returns_404(): void
    {
        $facility = $this->facility();
        $this->service($facility, 'dich-vu-nhap', 'draft');

        $this->get('/vi/kham-nha/bv-a/dich-vu-nhap')->assertNotFound();
    }

    public function test_service_under_the_wrong_facility_returns_404(): void
    {
        $first = $this->facility('bv-a');
        $this->service($first);

        $second = DentalFacility::create([
            'dental_category_id' => DentalCategory::first()->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $second->translateOrNew('vi')->fill(['name' => 'Bệnh viện B', 'slug' => 'bv-b']);
        $second->save();

        // Dịch vụ có thật nhưng không thuộc cơ sở trên đường dẫn
        $this->get('/vi/kham-nha/bv-b/boc-rang-su')->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/vi/kham-nha/khong-ton-tai')->assertNotFound();
    }
}
