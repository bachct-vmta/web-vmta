<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\Translations\SpecialtyTranslation;
use Packages\Core\Src\Models\MediaFile;
use Tests\TestCase;

class SpecialtyDetailTest extends TestCase
{
    use RefreshDatabase;

    private function specialty(array $specialtyAttrs = [], array $translationAttrs = []): Specialty
    {
        $sp = Specialty::create(array_merge([
            'sort_order' => 1,
            'is_active' => true,
            'show_lead_form' => true,
        ], $specialtyAttrs));

        SpecialtyTranslation::create(array_merge([
            'specialty_id' => $sp->id,
            'locale' => 'vi',
            'name' => 'Chuyên khoa thử',
            // Most tests use a neutral slug so dental source fallback does not affect
            // generic specialty assertions.
            'slug' => 'specialty-test',
        ], $translationAttrs));

        return $sp;
    }

    public function test_detail_returns_404_for_missing_slug(): void
    {
        $this->get('/vi/chuyen-khoa/khong-co')->assertNotFound();
    }

    public function test_detail_returns_404_when_inactive(): void
    {
        $this->specialty(['is_active' => false]);

        $this->get('/vi/chuyen-khoa/specialty-test')->assertNotFound();
    }

    public function test_detail_renders_all_sections_when_full_data(): void
    {
        $this->specialty([], [
            'hero_h1' => 'NHA KHOA',
            'intro_h2' => 'Chăm sóc răng miệng',
            'intro_lead' => 'Đội ngũ đầu ngành',
            'intro_body_html' => '<p>Body content</p>',
            'strengths_h2_line1' => 'Lợi thế',
            'strengths_json' => [
                ['title' => 'Bác sĩ giỏi', 'bullets' => ['10+ năm kinh nghiệm']],
            ],
            'hospitals_h2_line1' => 'Bệnh viện',
            'hospitals_json' => [
                ['name' => 'Nha khoa ABC', 'bullets' => ['Trung tâm Implant']],
            ],
            'lead_h2_line1' => 'Đăng ký tư vấn',
        ]);

        $r = $this->get('/vi/chuyen-khoa/specialty-test');
        $r->assertOk();
        $r->assertSee('NHA KHOA');
        $r->assertSee('Chăm sóc răng miệng');
        $r->assertSee('Bác sĩ giỏi');
        $r->assertSee('Nha khoa ABC');
        $r->assertSee('Đăng ký tư vấn');
    }

    public function test_detail_skips_empty_sections(): void
    {
        $this->specialty([], [
            'hero_h1' => 'NHA KHOA',
        ]);

        $r = $this->get('/vi/chuyen-khoa/specialty-test');
        $r->assertOk();
        $r->assertSee('NHA KHOA');
        $r->assertDontSee('Chăm sóc răng miệng');
        $r->assertDontSee('Bác sĩ giỏi');
        $r->assertDontSee('Nha khoa ABC');
    }

    public function test_lead_form_hidden_when_show_lead_form_false(): void
    {
        $this->specialty(['show_lead_form' => false]);

        $r = $this->get('/vi/chuyen-khoa/specialty-test');
        $r->assertOk();
        $r->assertDontSee('id="lead-form"', false);
    }

    public function test_seo_title_uses_translation_override(): void
    {
        $this->specialty([], ['seo_title' => 'Custom SEO Title']);

        $r = $this->get('/vi/chuyen-khoa/specialty-test');
        $r->assertOk();
        $r->assertSee('<title>Custom SEO Title', false);
    }

    public function test_detail_purifies_specialty_html_content(): void
    {
        $this->specialty([], [
            'intro_body_html' => '<p>Safe</p><a href="javascript:alert(1)" onclick="alert(2)">Bad link</a><script>alert(3)</script>',
            'lead_body_html' => '<p>Lead</p><a href="javascript:alert(4)" onclick="alert(5)">Lead bad</a>',
        ]);

        $r = $this->get('/vi/chuyen-khoa/specialty-test');
        $r->assertOk();
        $r->assertSee('<p>Safe</p>', false);
        $r->assertDontSee('javascript:alert', false);
        $r->assertDontSee('onclick=', false);
        $r->assertDontSee('<script>', false);
    }

    public function test_dental_source_fallback_does_not_override_custom_cms_content(): void
    {
        $hospitalImage = MediaFile::create([
            'name' => 'Dental hospital',
            'alt' => 'Dental hospital',
            'permalink' => 'dental-hospital.jpg',
            'mine_type' => 'image/jpeg',
        ]);

        $this->specialty([], [
            'slug' => 'nha-khoa',
            'intro_h2' => 'Tiêu đề nha khoa từ CMS',
            'intro_body_html' => '<p>Nội dung CMS riêng.</p>',
            'strengths_json' => [
                ['title' => 'CMS 1', 'image_path' => 'x/1.jpg'],
                ['title' => 'CMS 2', 'image_path' => 'x/2.jpg'],
                ['title' => 'CMS 3', 'image_path' => 'x/3.jpg'],
            ],
            'hospitals_json' => [
                ['name' => 'CMS Hospital', 'bullets' => ['CMS bullet'], 'image_media_id' => $hospitalImage->id],
            ],
        ]);

        $r = $this->get('/vi/chuyen-khoa/nha-khoa');
        $r->assertOk();
        $r->assertSee('Tiêu đề nha khoa từ CMS');
        $r->assertSee('CMS 1');
        $r->assertDontSee('Việt Nam – Điểm đến mới của nha khoa chất lượng cao');
        $r->assertDontSee('Implant (Trồng răng)');
        preg_match_all('/<h3[^>]*>CMS Hospital<\/h3>/', $r->getContent(), $hospitalTitles);
        $this->assertCount(4, $hospitalTitles[0]);
        $this->assertSame(4, substr_count($r->getContent(), '/uploads/dental-hospital.jpg'));
        $this->assertSame(2, substr_count($r->getContent(), '/images/specialties/nha-khoa/hero-bg.png'));
    }
}
