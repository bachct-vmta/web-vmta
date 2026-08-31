<?php

namespace Tests\Feature\Dental;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Packages\Content\Src\Models\Post;
use Packages\Dental\Database\Seeders\DentalCategorySeeder;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Models\DentalFacility;
use Packages\Dental\Src\Models\DentalService;
use Packages\Inquiry\Src\Models\Inquiry;
use Tests\TestCase;

class DentalInquiryAndNewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DentalCategorySeeder::class);
        Mail::fake();
    }

    private function service(): DentalService
    {
        $facility = DentalFacility::create([
            'dental_category_id' => DentalCategory::first()->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $facility->translateOrNew('vi')->fill(['name' => 'Bệnh viện A', 'slug' => 'bv-a']);
        $facility->save();

        $service = DentalService::create([
            'dental_facility_id' => $facility->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        $service->translateOrNew('vi')->fill(['title' => 'Bọc răng sứ', 'slug' => 'boc-rang-su']);
        $service->save();

        return $service;
    }

    public function test_dental_service_morph_alias_is_registered(): void
    {
        $this->assertSame(DentalService::class, Relation::getMorphedModel('dental_service'));
    }

    public function test_service_page_renders_the_consultation_cta(): void
    {
        $service = $this->service();

        $this->get('/vi/kham-nha/bv-a/boc-rang-su')
            ->assertOk()
            // Nhãn viết hoa bằng CSS `uppercase`, HTML giữ nguyên chữ thường
            ->assertSee('Liên hệ để được tư vấn')
            ->assertSee('name="source_ref_type" value="dental_service"', false)
            ->assertSee('name="source_ref_id" value="'.$service->id.'"', false);
    }

    public function test_quick_inquiry_accepts_a_dental_service_reference(): void
    {
        $service = $this->service();

        $response = $this->post('/vi/inquiry/quick', [
            'name' => 'Nguyễn Văn A',
            'phone' => '0901234567',
            'email' => 'a@example.com',
            'message' => 'Tư vấn bọc răng sứ',
            'source_ref_type' => 'dental_service',
            'source_ref_id' => $service->id,
            'consent_given' => '1',
        ]);

        $response->assertSessionHasNoErrors();

        $inquiry = Inquiry::latest('id')->first();
        $this->assertNotNull($inquiry);
        $this->assertSame('dental_service', $inquiry->source_ref_type);
        $this->assertSame($service->id, (int) $inquiry->source_ref_id);
    }

    public function test_quick_inquiry_still_rejects_an_unknown_reference_type(): void
    {
        $service = $this->service();

        $this->post('/vi/inquiry/quick', [
            'name' => 'Nguyễn Văn A',
            'phone' => '0901234567',
            'source_ref_type' => 'khong_ton_tai',
            'source_ref_id' => $service->id,
            'consent_given' => '1',
        ])->assertSessionHasErrors('source_ref_type');
    }

    public function test_news_sidebar_shows_published_posts(): void
    {
        $this->service();

        $post = Post::create(['status' => 'published', 'published_at' => now()->subDay()]);
        $post->translateOrNew('vi')->fill(['title' => 'Tin nha khoa mới', 'slug' => 'tin-nha-khoa-moi']);
        $post->save();

        $this->get('/vi/kham-nha/bv-a/boc-rang-su')
            ->assertOk()
            ->assertSee('Tin Tức')
            ->assertSee('Tin nha khoa mới');
    }

    public function test_service_page_renders_without_any_post(): void
    {
        $this->service();

        $this->get('/vi/kham-nha/bv-a/boc-rang-su')
            ->assertOk()
            ->assertDontSee('Xem thêm');
    }

    public function test_content_and_inquiry_do_not_import_dental_classes(): void
    {
        foreach (['packages/Content/src', 'packages/Inquiry/src', 'packages/Catalog/src'] as $dir) {
            $hits = shell_exec('grep -rl "Packages\\\\\\\\Dental" '.base_path($dir).' 2>/dev/null');
            $this->assertEmpty(trim((string) $hits), $dir.' không được import class của Dental');
        }
    }
}
