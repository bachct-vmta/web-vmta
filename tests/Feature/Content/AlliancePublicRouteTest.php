<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Repositories\Interfaces\AllianceSectionRepositoryInterface;
use Tests\TestCase;

class AlliancePublicRouteTest extends TestCase
{
    use RefreshDatabase;

    private function seedAlliance(): void
    {
        $repo = app(AllianceSectionRepositoryInterface::class);

        $repo->upsertSection(AllianceSectionPosition::Hero, [], [
            'vi' => ['title' => 'Mạng lưới Liên minh Du lịch Y tế VMTA'],
            'en' => ['title' => 'VMTA Medical Tourism Alliance Network'],
        ]);
        $repo->upsertSection(AllianceSectionPosition::Overview, [], [
            'vi' => ['title' => 'TỔNG QUAN MẠNG LƯỚI', 'body' => 'Nội dung tổng quan.'],
            'en' => ['title' => 'NETWORK OVERVIEW', 'body' => 'Overview body.'],
        ]);
        $repo->upsertSection(AllianceSectionPosition::Standards, [], [
            'vi' => ['title' => 'TIÊU CHUẨN LIÊN MINH', 'items' => array_map(
                fn ($i) => ['icon' => "/images/alliance/asset-{$i}.jpg", 'label' => "Tiêu chuẩn {$i}", 'description' => "Mô tả {$i}"],
                range(1, 5)
            )],
            'en' => ['title' => 'ALLIANCE STANDARDS', 'items' => array_map(
                fn ($i) => ['icon' => "/images/alliance/asset-{$i}.jpg", 'label' => "Standard {$i}", 'description' => "Desc {$i}"],
                range(1, 5)
            )],
        ]);
        $repo->upsertSection(AllianceSectionPosition::Map, [], [
            'vi' => ['title' => 'BẢN ĐỒ MẠNG LƯỚI'],
            'en' => ['title' => 'NETWORK MAP'],
        ]);
        $repo->upsertSection(AllianceSectionPosition::JoinForm, [], [
            'vi' => ['title' => 'Tham Gia Liên Minh', 'cta_label' => 'Gửi đăng ký'],
            'en' => ['title' => 'Join the Alliance', 'cta_label' => 'Submit'],
        ]);
    }

    public function test_vi_alliance_route_returns_200_with_5_sections(): void
    {
        $this->seedAlliance();

        $response = $this->get('/vi/mang-luoi-lien-minh');

        $response->assertOk();
        $response->assertSee('Mạng lưới Liên minh Du lịch Y tế VMTA', false);
        $response->assertSee('Tổng quan mạng lưới', false);
        $response->assertSee('Tiêu chuẩn liên minh', false);
        $response->assertSee('Bản đồ mạng lưới', false);
        $response->assertSee('Tham Gia liên minh', false);
    }

    public function test_en_alliance_route_returns_200(): void
    {
        $this->seedAlliance();

        $response = $this->get('/en/alliance-network');

        $response->assertOk();
        $response->assertSee('VMTA Medical Tourism Alliance Network', false);
        $response->assertSee('Network overview', false);
    }

    public function test_vi_locale_does_not_serve_en_slug(): void
    {
        $this->seedAlliance();
        $this->get('/vi/alliance-network')->assertNotFound();
    }

    public function test_en_locale_does_not_serve_vi_slug(): void
    {
        $this->seedAlliance();
        $this->get('/en/mang-luoi-lien-minh')->assertNotFound();
    }

    public function test_standards_section_renders_source_assessment_items(): void
    {
        $this->seedAlliance();

        $response = $this->get('/vi/mang-luoi-lien-minh');

        $response->assertSee('Năng lực chuyên môn và đội ngũ', false);
        $response->assertSee('Hệ thống vận hành &amp; chất lượng dịch vụ', false);
        $response->assertSee('Cơ sở hạ tầng và trải nghiệm khách hàng', false);
        $response->assertSee('Khả năng phục vụ khách quốc tế', false);
    }

    public function test_form_action_points_to_partner_route(): void
    {
        $this->seedAlliance();

        $response = $this->get('/vi/mang-luoi-lien-minh');

        $response->assertSee('/vi/lien-he/doi-tac', false);
        $response->assertSee('name="valid_from"', false);
        $response->assertSee('autocomplete="nope"', false);
    }

    public function test_layout_assets_are_referenced(): void
    {
        $this->seedAlliance();

        $response = $this->get('/vi/mang-luoi-lien-minh');

        $response->assertSee('/images/alliance/hero.png', false);
        $response->assertSee('/images/alliance/standards.jpg', false);
        $response->assertSee('/images/alliance/background.png', false);
        $response->assertSee('/images/alliance/map.jpg', false);
        $response->assertSee('/images/alliance/join.jpg', false);
    }

    public function test_homepage_still_renders_200(): void
    {
        $this->get('/vi/')->assertOk();
    }

    public function test_about_route_still_renders_200(): void
    {
        $this->get('/vi/gioi-thieu')->assertOk();
    }

    public function test_posts_index_still_renders_200(): void
    {
        $this->get('/vi/tin-tuc')->assertOk();
    }

    public function test_catch_all_does_not_shadow_alliance_slug(): void
    {
        $this->seedAlliance();
        $response = $this->get('/vi/mang-luoi-lien-minh');
        $response->assertOk();
        // Catch-all PageController would return 404 because no Page with this slug exists.
        // If we get 200, alliance route won.
    }
}
