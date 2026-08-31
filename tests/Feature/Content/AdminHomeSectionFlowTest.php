<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Packages\Content\Database\Seeders\HomeSectionSeeder;
use Packages\Content\Src\Models\HomeSection;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class AdminHomeSectionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(HomeSectionSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@nguyenkhoi.dev')->first();
    }

    private function coordinator(): User
    {
        return User::where('email', 'coordinator@nguyenkhoi.dev')->first();
    }

    // -------------------------------------------------------------------------
    // Admin index
    // -------------------------------------------------------------------------

    public function test_admin_index_requires_authentication(): void
    {
        $this->get('/admin/home')->assertRedirect();
    }

    public function test_admin_index_renders_8_position_tabs_not_9(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/home');
        $response->assertOk();

        foreach (['hero', 'values', 'about', 'solutions', 'vision_mission', 'benefits', 'technology', 'why_vn'] as $pos) {
            $response->assertSee('data-home-fieldset="'.$pos.'"', false);
        }
    }

    public function test_admin_index_does_not_include_stats_or_cta_join_tabs(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/home');
        $response->assertOk();

        foreach (['stats', 'cta_join', 'vision', 'mission', 'video'] as $dropped) {
            $response->assertDontSee('data-home-fieldset="'.$dropped.'"', false);
        }
    }

    public function test_admin_index_includes_vision_mission_benefits_technology_why_vn_tabs(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/home');
        $response->assertOk();

        foreach (['vision_mission', 'benefits', 'technology', 'why_vn'] as $newTab) {
            $response->assertSee('data-home-fieldset="'.$newTab.'"', false);
        }
    }

    // -------------------------------------------------------------------------
    // Hero — marquee (variable cardinality 4-10)
    // -------------------------------------------------------------------------

    public function test_admin_can_update_hero_with_4_marquee_items(): void
    {
        $items = array_fill(0, 4, ['label' => 'USA', 'value' => '10 000']);
        $response = $this->actingAs($this->admin())->put('/admin/home/hero', [
            'translations' => [
                'vi' => ['title' => 'Hero VI', 'items' => $items],
                'en' => ['title' => 'Hero EN', 'items' => $items],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('errors');
        $section = HomeSection::where('position', 'hero')->first();
        $this->assertCount(4, $section->translate('vi')->items);
    }

    public function test_admin_can_update_hero_with_10_marquee_items(): void
    {
        $items = array_fill(0, 10, ['label' => 'Country', 'value' => '999']);
        $response = $this->actingAs($this->admin())->put('/admin/home/hero', [
            'translations' => [
                'vi' => ['title' => 'Hero VI', 'items' => $items],
                'en' => ['title' => 'Hero EN', 'items' => $items],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('errors');
    }

    public function test_admin_update_hero_with_3_marquee_items_returns_422(): void
    {
        $items = array_fill(0, 3, ['label' => 'X', 'value' => 'Y']);
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/hero', [
                'translations' => [
                    'vi' => ['title' => 'Hero VI', 'items' => $items],
                    'en' => ['title' => 'Hero EN', 'items' => $items],
                ],
            ]);

        $response->assertSessionHasErrors(['translations.vi.items', 'translations.en.items']);
    }

    public function test_admin_update_hero_with_11_marquee_items_returns_422(): void
    {
        $items = array_fill(0, 11, ['label' => 'X', 'value' => 'Y']);
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/hero', [
                'translations' => [
                    'vi' => ['title' => 'Hero VI', 'items' => $items],
                    'en' => ['title' => 'Hero EN', 'items' => $items],
                ],
            ]);

        $response->assertSessionHasErrors(['translations.vi.items', 'translations.en.items']);
    }

    public function test_admin_update_hero_marquee_item_missing_value_returns_422(): void
    {
        $items = [['label' => 'USA'], ['label' => 'France'], ['label' => 'Germany'], ['label' => 'Cambodia']];
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/hero', [
                'translations' => [
                    'vi' => ['title' => 'Hero VI', 'items' => $items],
                    'en' => ['title' => 'Hero EN', 'items' => $items],
                ],
            ]);

        $response->assertSessionHasErrors();
    }

    // -------------------------------------------------------------------------
    // VisionMission — video_url + 3 audience cards
    // -------------------------------------------------------------------------

    public function test_admin_can_update_vision_mission_with_3_audience_items_and_video(): void
    {
        $items = [
            ['audience' => 'Bệnh nhân', 'body' => 'Nội dung 1'],
            ['audience' => 'Bệnh viện', 'body' => 'Nội dung 2'],
            ['audience' => 'Nhà đầu tư', 'body' => 'Nội dung 3'],
        ];
        $response = $this->actingAs($this->admin())->put('/admin/home/vision_mission', [
            'video_url' => 'https://player.vimeo.com/video/123456',
            'translations' => [
                'vi' => ['title' => 'Tầm nhìn', 'subtitle' => 'Sứ mệnh', 'body' => 'Nội dung tầm nhìn', 'items' => $items],
                'en' => ['title' => 'Vision', 'subtitle' => 'Mission', 'body' => 'Vision body', 'items' => $items],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('errors');
        $section = HomeSection::where('position', 'vision_mission')->first();
        $this->assertCount(3, $section->translate('vi')->items);
        $this->assertSame('https://player.vimeo.com/video/123456', $section->video_url);
    }

    public function test_admin_vision_mission_video_url_accepts_m3u8(): void
    {
        $items = [
            ['audience' => 'A', 'body' => 'B'],
            ['audience' => 'C', 'body' => 'D'],
            ['audience' => 'E', 'body' => 'F'],
        ];
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/vision_mission', [
                'video_url' => 'https://storageovp.vnews.gov.vn/videos/stream.m3u8',
                'translations' => [
                    'vi' => ['title' => 'V', 'items' => $items],
                    'en' => ['title' => 'V', 'items' => $items],
                ],
            ]);

        $response->assertSessionMissing('errors');
    }

    public function test_admin_vision_mission_video_url_accepts_youtube_embed(): void
    {
        $items = [
            ['audience' => 'A', 'body' => 'B'],
            ['audience' => 'C', 'body' => 'D'],
            ['audience' => 'E', 'body' => 'F'],
        ];
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/vision_mission', [
                'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'translations' => [
                    'vi' => ['title' => 'V', 'items' => $items],
                    'en' => ['title' => 'V', 'items' => $items],
                ],
            ]);

        $response->assertSessionMissing('errors');
    }

    public function test_admin_vision_mission_video_url_rejects_evil_iframe(): void
    {
        $items = [
            ['audience' => 'A', 'body' => 'B'],
            ['audience' => 'C', 'body' => 'D'],
            ['audience' => 'E', 'body' => 'F'],
        ];
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/vision_mission', [
                'video_url' => 'https://evil.com/iframe',
                'translations' => [
                    'vi' => ['title' => 'V', 'items' => $items],
                    'en' => ['title' => 'V', 'items' => $items],
                ],
            ]);

        $response->assertSessionHasErrors('video_url');
    }

    // -------------------------------------------------------------------------
    // Benefits — 2 rows with bullets sub-array
    // -------------------------------------------------------------------------

    public function test_admin_can_update_benefits_with_2_rows_each_with_bullets(): void
    {
        $items = [
            ['audience' => 'Bệnh nhân', 'subtitle' => 'Tiêu đề 1', 'bullets' => ['Điểm 1', 'Điểm 2'], 'image_url' => 'https://example.com/img1.jpg'],
            ['audience' => 'Bệnh viện', 'subtitle' => 'Tiêu đề 2', 'bullets' => ['Điểm A', 'Điểm B'], 'image_url' => null],
        ];
        $response = $this->actingAs($this->admin())->put('/admin/home/benefits', [
            'translations' => [
                'vi' => ['title' => 'Lợi ích', 'items' => $items],
                'en' => ['title' => 'Benefits', 'items' => $items],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('errors');
        $section = HomeSection::where('position', 'benefits')->first();
        $this->assertCount(2, $section->translate('vi')->items);
        $this->assertSame(['Điểm 1', 'Điểm 2'], $section->translate('vi')->items[0]['bullets']);
    }

    public function test_admin_update_benefits_with_1_row_returns_422(): void
    {
        $items = [
            ['audience' => 'Bệnh nhân', 'subtitle' => 'Sub', 'bullets' => ['B1'], 'image_url' => null],
        ];
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/benefits', [
                'translations' => [
                    'vi' => ['title' => 'X', 'items' => $items],
                    'en' => ['title' => 'X', 'items' => $items],
                ],
            ]);

        $response->assertSessionHasErrors(['translations.vi.items', 'translations.en.items']);
    }

    public function test_admin_update_benefits_row_with_0_bullets_returns_422(): void
    {
        $items = [
            ['audience' => 'A', 'subtitle' => 'S', 'bullets' => [], 'image_url' => null],
            ['audience' => 'B', 'subtitle' => 'T', 'bullets' => ['ok'], 'image_url' => null],
        ];
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/benefits', [
                'translations' => [
                    'vi' => ['title' => 'X', 'items' => $items],
                    'en' => ['title' => 'X', 'items' => $items],
                ],
            ]);

        $response->assertSessionHasErrors();
    }

    // -------------------------------------------------------------------------
    // Technology — 2 subgroups with bullets
    // -------------------------------------------------------------------------

    public function test_admin_can_update_technology_with_2_subgroups_with_bullets(): void
    {
        $items = [
            ['title' => 'Nhóm 1', 'bullets' => ['Công nghệ A', 'Công nghệ B']],
            ['title' => 'Nhóm 2', 'bullets' => ['Công nghệ C']],
        ];
        $response = $this->actingAs($this->admin())->put('/admin/home/technology', [
            'translations' => [
                'vi' => ['title' => 'Công nghệ', 'items' => $items],
                'en' => ['title' => 'Technology', 'items' => $items],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('errors');
        $section = HomeSection::where('position', 'technology')->first();
        $this->assertCount(2, $section->translate('vi')->items);
    }

    // -------------------------------------------------------------------------
    // WhyVN — 3 icon cards
    // -------------------------------------------------------------------------

    public function test_admin_can_update_why_vn_with_3_icon_cards(): void
    {
        $items = [
            ['icon' => 'star.svg', 'title' => 'Lý do 1', 'body' => 'Nội dung 1'],
            ['icon' => 'heart.svg', 'title' => 'Lý do 2', 'body' => 'Nội dung 2'],
            ['icon' => 'check.svg', 'title' => 'Lý do 3', 'body' => 'Nội dung 3'],
        ];
        $response = $this->actingAs($this->admin())->put('/admin/home/why_vn', [
            'translations' => [
                'vi' => ['title' => 'Tại sao VN', 'items' => $items],
                'en' => ['title' => 'Why VN', 'items' => $items],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('errors');
        $section = HomeSection::where('position', 'why_vn')->first();
        $this->assertCount(3, $section->translate('vi')->items);
        $this->assertSame('Lý do 1', $section->translate('vi')->items[0]['title']);
    }

    // -------------------------------------------------------------------------
    // Security regression
    // -------------------------------------------------------------------------

    public function test_admin_update_strips_script_from_body(): void
    {
        $items = [
            ['audience' => 'A', 'body' => 'B'],
            ['audience' => 'C', 'body' => 'D'],
            ['audience' => 'E', 'body' => 'F'],
        ];
        $this->actingAs($this->admin())->put('/admin/home/vision_mission', [
            'translations' => [
                'vi' => ['title' => 'OK', 'body' => 'Hello <script>alert(1)</script> world', 'items' => $items],
                'en' => ['title' => 'OK', 'body' => 'Hello <script>alert(1)</script> world', 'items' => $items],
            ],
        ]);

        $section = HomeSection::where('position', 'vision_mission')->first();
        $this->assertStringNotContainsString('<script>', (string) $section->translate('vi')->body);
        $this->assertStringContainsString('Hello', (string) $section->translate('vi')->body);
    }

    public function test_admin_update_rejects_javascript_cta_url(): void
    {
        $items = array_fill(0, 4, ['label' => 'X', 'value' => 'Y']);
        $response = $this->actingAs($this->admin())
            ->from('/admin/home')
            ->put('/admin/home/hero', [
                'translations' => [
                    'vi' => ['title' => 'OK', 'cta_url' => 'javascript:alert(1)', 'items' => $items],
                    'en' => ['title' => 'OK', 'cta_url' => 'javascript:alert(1)', 'items' => $items],
                ],
            ]);

        $response->assertSessionHasErrors(['translations.vi.cta_url', 'translations.en.cta_url']);
    }

    // -------------------------------------------------------------------------
    // Permission
    // -------------------------------------------------------------------------

    public function test_coordinator_blocked_403(): void
    {
        $response = $this->actingAs($this->coordinator())->get('/admin/home');
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Side-effects
    // -------------------------------------------------------------------------

    public function test_update_invalidates_cache(): void
    {
        Cache::put('content:home:render:vi', ['warm' => true], 60);

        $items = array_fill(0, 4, ['label' => 'X', 'value' => 'Y']);
        $this->actingAs($this->admin())->put('/admin/home/hero', [
            'translations' => [
                'vi' => ['title' => 'Mới', 'items' => $items],
                'en' => ['title' => 'New', 'items' => $items],
            ],
        ]);

        $this->assertFalse(Cache::has('content:home:render:vi'));
    }

    public function test_update_creates_activity_log(): void
    {
        $items = array_fill(0, 4, ['label' => 'X', 'value' => 'Y']);
        $this->actingAs($this->admin())->put('/admin/home/hero', [
            'translations' => [
                'vi' => ['title' => 'Log test', 'items' => $items],
                'en' => ['title' => 'Log test EN', 'items' => $items],
            ],
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'home_section.updated',
            'user_id' => $this->admin()->id,
        ]);
    }

    public function test_update_one_position_does_not_affect_others(): void
    {
        $heroBefore = HomeSection::where('position', 'hero')->first()->translate('vi')->title;

        $items = [
            ['icon' => 'a', 'title' => 'A', 'body' => 'B'],
            ['icon' => 'b', 'title' => 'C', 'body' => 'D'],
            ['icon' => 'c', 'title' => 'E', 'body' => 'F'],
        ];
        $this->actingAs($this->admin())->put('/admin/home/why_vn', [
            'translations' => [
                'vi' => ['title' => 'Tại sao VN', 'items' => $items],
                'en' => ['title' => 'Why VN', 'items' => $items],
            ],
        ]);

        $heroAfter = HomeSection::where('position', 'hero')->first()->translate('vi')->title;
        $this->assertSame($heroBefore, $heroAfter);
    }
}
