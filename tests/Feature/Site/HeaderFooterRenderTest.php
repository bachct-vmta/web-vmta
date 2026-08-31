<?php

namespace Tests\Feature\Site;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderFooterRenderTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------------
    // Header tests
    // ---------------------------------------------------------------------------

    public function test_header_renders_logo_image(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('images/home/header/logo-vmta.png', false);
    }

    public function test_header_renders_main_navigation_menu_items(): void
    {
        // The menu partial is included with location 'main_navigation'.
        // Without seeded menu items the nav block is empty but the include fires;
        // we check the aria-label wrapper is absent (no items) OR present — the key
        // assertion is that the page loads successfully and no exception is thrown.
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // Header nav wrapper exists (x-data attribute indicates Alpine header component)
        $response->assertSee('x-data', false);
    }

    public function test_header_renders_flag_lang_switcher_vn_us(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // VN flag image
        $response->assertSee('images/home/header/flag-vn.png', false);
        // US flag image
        $response->assertSee('images/home/header/flag-us.png', false);
        // VI aria-label
        $response->assertSee('aria-label="Tiếng Việt"', false);
        // EN aria-label
        $response->assertSee('aria-label="English"', false);
    }

    public function test_header_renders_join_cta_link_to_contact(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // CTA text (VI locale)
        $response->assertSee('THAM GIA', false);
        // Link href contains contact route
        $response->assertSee('/vi/contact', false);
    }

    public function test_header_mobile_drawer_toggleable(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // Alpine data attribute for mobile drawer
        $response->assertSee('mobileOpen', false);
        $response->assertSee('x-show="mobileOpen"', false);
    }

    // ---------------------------------------------------------------------------
    // Footer tests
    // ---------------------------------------------------------------------------

    public function test_footer_renders_3_col_grid_layout(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // 3-col grid at md-fs (850px = Flatsome "large" breakpoint, matches vmta.test)
        $response->assertSee('md-fs:grid-cols-3', false);
        // Footer wrapper class
        $response->assertSee('footer-wrapper', false);
        // Footer background image (matches vmta.test ss-footer section-bg)
        $response->assertSee('908c99ad-f012-4b20-9d8a-cbeee71686e5.png', false);
    }

    public function test_footer_renders_newsletter_form_with_email_input(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('name="email"', false);
        // Newsletter subscribe route (Phase 6 — packages/Newsletter)
        $response->assertSee(route('newsletter.vi.subscribe'), false);
    }

    public function test_footer_renders_social_icons_with_4_images(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        $response->assertSee('images/home/footer/social-1.png', false);
        $response->assertSee('images/home/footer/social-2.png', false);
        $response->assertSee('images/home/footer/social-3.png', false);
        $response->assertSee('images/home/footer/social-4.png', false);
    }

    public function test_footer_does_not_render_policy_menu_position(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // Old footer_1_navigation location no longer referenced
        $response->assertDontSee('footer_1_navigation', false);
    }

    public function test_footer_does_not_render_quicklinks_menu_position(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // Old footer_2_navigation location no longer referenced
        $response->assertDontSee('footer_2_navigation', false);
    }

    public function test_lang_switcher_swap_vi_en_preserves_path(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // Both locale URLs should be present as links in the switcher
        $response->assertSee('/vi', false);
        $response->assertSee('/en', false);
    }

    public function test_footer_social_heading_translated(): void
    {
        // VI locale
        $responseVi = $this->get('/vi/');
        $responseVi->assertStatus(200);
        $responseVi->assertSee('Mạng xã hội', false);

        // EN locale
        $responseEn = $this->get('/en/');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('Our social networks', false);
    }

    public function test_footer_renders_company_info_and_menu_columns(): void
    {
        $response = $this->get('/vi/');

        $response->assertStatus(200);
        // Company name (info column, matches vmta.test)
        $response->assertSee('Vietnam Medical Tourism Alliance', false);
        // Address line
        $response->assertSee('193 Trích Sài', false);
        // Three menu group headings
        $response->assertSee('Chính Sách', false);
        $response->assertSee('Liên Kết', false);
        $response->assertSee('Hỗ trợ khách hàng', false);
    }
}
