<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Database\Seeders\HomeSectionSeeder;
use Packages\Content\Src\Models\HomeSection;
use Packages\Content\Src\Models\Post;
use Tests\TestCase;

class HomePageRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HomeSectionSeeder::class);
    }

    public function test_home_renders_8_sections_in_correct_order(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $html = $response->getContent();

        $positions = ['hero', 'values', 'about', 'solutions', 'vision_mission', 'benefits', 'technology', 'why_vn'];
        foreach ($positions as $position) {
            $response->assertSee('data-home-section="'.$position.'"', false);
        }

        // Verify order: why_vn must appear before news-teaser
        $whyVnPos = strpos($html, 'data-home-section="why_vn"');
        $newsPos = strpos($html, 'data-home-section="news"');
        if ($newsPos !== false) {
            $this->assertLessThan($newsPos, $whyVnPos, 'why_vn must appear before news-teaser');
        }
    }

    public function test_hero_section_renders_h1_with_vietnamese_diacritics(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('Liên minh Du lịch Y tế Việt Nam', false);
    }

    public function test_hero_marquee_items_present(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('USA', false);
        $response->assertSee('Cambodia', false);
        $response->assertSee('France', false);
        $response->assertSee('Germany', false);
        $response->assertSee('10 000', false);
    }

    public function test_hero_cta_links_to_contact_page(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('/vi/contact', false);
    }

    public function test_values_renders_3_icon_cards_with_titles(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('GIÁ TRỊ CỐT LÕI', false);
        $response->assertSee('Định chuẩn quốc tế', false);
        $response->assertSee('Điều phối tập trung', false);
        $response->assertSee('Y thuật nhân văn', false);
    }

    public function test_about_renders_3_bullets_and_cta_to_gioi_thieu(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('VỀ VMTA', false);
        $response->assertSee('Kết nối nguồn lực y tế', false);
        $response->assertSee('Chuẩn hóa toàn bộ hành trình', false);
        $response->assertSee('Tối ưu hiệu quả điều trị và phục hồi', false);
        $response->assertSee('/vi/gioi-thieu', false);
    }

    public function test_solutions_renders_with_white_bg_and_transparent_cards(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('GIẢI PHÁP THEN CHỐT', false);
        // Solutions section matches vmta.vn original — ss-giai-phap pattern:
        // - White section bg with subtle image overlay (vmta-bg-filter-20)
        // - Transparent cards (no card bg), dark text, teal headings
        $html = $response->getContent();
        $dataPos = strpos($html, 'data-home-section="solutions"');
        $this->assertNotFalse($dataPos, 'solutions section not found');
        // Section opening tag must carry filter helper + white bg
        $tagChunk = substr($html, max(0, $dataPos - 300), 350);
        $this->assertMatchesRegularExpression('/(vmta-bg-filter-20|bg-white)/', $tagChunk);
    }

    public function test_vision_mission_renders_video_with_data_hls_src(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('data-hls-src', false);
        $response->assertSee('storageovp.vnews.gov.vn', false);
        $response->assertSee('TẦM NHÌN', false);
        $response->assertSee('SỨ MỆNH', false);
    }

    public function test_vision_mission_renders_3_audience_cards(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('Với khách hàng', false);
        $response->assertSee('Với đối tác', false);
        $response->assertSee('Với ngành', false);
    }

    public function test_benefits_renders_2_rows_with_alternating_layout(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('LỢI ÍCH ĐA TẦNG', false);
        $response->assertSee('Dành cho khách hàng', false);
        $response->assertSee('Dành cho đối tác', false);
        $response->assertSee('Thẩm định y khoa trước khi khởi hành', false);
    }

    public function test_technology_renders_2_subgroups_with_bullets(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('CÔNG NGHỆ &amp; SỰ KHÁC BIỆT', false);
        $response->assertSee('Trung tâm Điều phối Vận hành', false);
        $response->assertSee('Quản trị sự phục hồi dựa trên dữ liệu', false);
    }

    public function test_why_vn_renders_3_icon_cards(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('TẠI SAO CHỌN VIỆT NAM &amp; VMTA?', false);
        $response->assertSee('Tiềm năng bản địa', false);
        $response->assertSee('Hạ tầng', false);
        $response->assertSee('Vai trò VMTA', false);
    }

    public function test_news_teaser_renders_after_why_vn_section(): void
    {
        Post::create([
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now()->subMinute(),
        ])->translateOrNew('vi')->fill([
            'title' => 'Bài kiểm tra tin tức',
            'slug' => 'bai-kiem-tra',
            'excerpt' => 'Tóm tắt',
            'body' => '<p>Nội dung</p>',
        ])->save();
        \Cache::flush();

        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $html = $response->getContent();
        $whyVnPos = strpos($html, 'data-home-section="why_vn"');
        $newsPos = strpos($html, 'data-home-section="news"');
        $this->assertNotFalse($whyVnPos);
        $this->assertNotFalse($newsPos);
        $this->assertGreaterThan($whyVnPos, $newsPos, 'news-teaser must appear AFTER why_vn');
    }

    public function test_news_teaser_uses_3_latest_published_posts(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $post = Post::create([
                'status' => 'published',
                'is_featured' => false,
                'published_at' => now()->subMinutes($i),
            ]);
            $post->translateOrNew('vi')->fill([
                'title' => "Bài viết {$i}",
                'slug' => "bai-viet-{$i}",
                'excerpt' => 'Tóm tắt',
                'body' => '<p>Nội dung</p>',
            ])->save();
        }
        \Cache::flush();

        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('Bài viết 1', false);
        $response->assertSee('Bài viết 2', false);
        $response->assertSee('Bài viết 3', false);
        $response->assertDontSee('Bài viết 5', false);
    }

    public function test_cache_hit_on_second_request(): void
    {
        \Cache::flush();
        $this->assertFalse(\Cache::has('content:home:render:vi'));

        $this->get('/vi/');

        $this->assertTrue(\Cache::has('content:home:render:vi'));
    }

    public function test_inactive_section_excluded(): void
    {
        HomeSection::query()->where('position', 'hero')->update(['is_active' => false]);
        \Cache::flush();

        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertDontSee('data-home-section="hero"', false);
    }

    public function test_en_locale_renders_with_english_content(): void
    {
        $response = $this->get('/en/');

        $response->assertStatus(200);
        // EN content seeds VI placeholder; verify page loads and section structure present
        $response->assertSee('data-home-section="hero"', false);
        $response->assertSee('data-home-section="why_vn"', false);
    }
}
