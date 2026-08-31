<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Models\AllianceSection;
use Packages\Content\Src\Repositories\Interfaces\AllianceSectionRepositoryInterface;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class AdminAllianceSectionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seedAlliance();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@nguyenkhoi.dev')->first();
    }

    private function coordinator(): User
    {
        return User::where('email', 'coordinator@nguyenkhoi.dev')->first();
    }

    private function seedAlliance(): void
    {
        $repo = app(AllianceSectionRepositoryInterface::class);
        foreach (AllianceSectionPosition::cases() as $p) {
            $repo->upsertSection($p, [], [
                'vi' => ['title' => $p->value.'-vi'],
                'en' => ['title' => $p->value.'-en'],
            ]);
        }
    }

    public function test_admin_index_requires_authentication(): void
    {
        $this->get('/admin/alliance')->assertRedirect();
    }

    public function test_admin_index_returns_200_for_super_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/alliance')
            ->assertOk();
    }

    public function test_admin_index_returns_403_for_coordinator_without_permission(): void
    {
        // Coordinator role does not include alliance.manage permission.
        $this->actingAs($this->coordinator())
            ->get('/admin/alliance')
            ->assertForbidden();
    }

    public function test_admin_index_renders_5_position_fieldsets(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/alliance');
        $response->assertOk();

        foreach (['hero', 'overview', 'standards', 'map', 'join_form'] as $pos) {
            $response->assertSee('data-alliance-fieldset="'.$pos.'"', false);
        }
    }

    public function test_admin_can_update_hero_title(): void
    {
        $payload = [
            'translations' => [
                'vi' => ['title' => 'Hero VI updated', 'subtitle' => 'sub vi'],
                'en' => ['title' => 'Hero EN updated', 'subtitle' => 'sub en'],
            ],
        ];

        $response = $this->actingAs($this->admin())->put('/admin/alliance/hero', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('status');

        $section = AllianceSection::query()->where('position', 'hero')->first();
        $this->assertSame('Hero VI updated', $section->translate('vi')->title);
        $this->assertSame('Hero EN updated', $section->translate('en')->title);
    }

    public function test_admin_update_standards_requires_exactly_5_items(): void
    {
        $items = array_map(fn ($i) => ['icon' => "ico-{$i}", 'label' => "L{$i}"], range(1, 5));
        $response = $this->actingAs($this->admin())->put('/admin/alliance/standards', [
            'translations' => [
                'vi' => ['title' => 'T', 'items' => $items],
                'en' => ['title' => 'T', 'items' => $items],
            ],
        ]);
        $response->assertRedirect();

        $section = AllianceSection::query()->where('position', 'standards')->first();
        $this->assertCount(5, $section->translate('vi')->items);
    }

    public function test_admin_update_standards_rejects_4_items(): void
    {
        $items = array_map(fn ($i) => ['icon' => "ico-{$i}", 'label' => "L{$i}"], range(1, 4));
        $response = $this->actingAs($this->admin())->put('/admin/alliance/standards', [
            'translations' => [
                'vi' => ['title' => 'T', 'items' => $items],
                'en' => ['title' => 'T', 'items' => $items],
            ],
        ]);
        $response->assertSessionHasErrors();
    }

    public function test_admin_update_standards_rejects_6_items(): void
    {
        $items = array_map(fn ($i) => ['icon' => "ico-{$i}", 'label' => "L{$i}"], range(1, 6));
        $response = $this->actingAs($this->admin())->put('/admin/alliance/standards', [
            'translations' => [
                'vi' => ['title' => 'T', 'items' => $items],
                'en' => ['title' => 'T', 'items' => $items],
            ],
        ]);
        $response->assertSessionHasErrors();
    }

    public function test_admin_update_purifies_body_html(): void
    {
        $dirty = 'Safe text <script>alert(1)</script>';
        $response = $this->actingAs($this->admin())->put('/admin/alliance/overview', [
            'translations' => [
                'vi' => ['title' => 'OV', 'body' => $dirty],
                'en' => ['title' => 'OV', 'body' => $dirty],
            ],
        ]);
        $response->assertRedirect();

        $section = AllianceSection::query()->where('position', 'overview')->first();
        $this->assertStringNotContainsString('<script>', $section->translate('vi')->body);
        $this->assertStringContainsString('Safe text', $section->translate('vi')->body);
    }

    public function test_admin_update_invalidates_cache(): void
    {
        $service = app(\Packages\Content\Src\Services\AlliancePageService::class);
        $service->getRenderData('vi');
        $this->assertTrue(Cache::has('content:alliance:render:vi'));

        $this->actingAs($this->admin())->put('/admin/alliance/hero', [
            'translations' => [
                'vi' => ['title' => 'X'],
                'en' => ['title' => 'X'],
            ],
        ]);

        $this->assertFalse(Cache::has('content:alliance:render:vi'));
    }

    public function test_admin_update_invalid_position_returns_404(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/alliance/nonexistent', [
                'translations' => ['vi' => ['title' => 'X'], 'en' => ['title' => 'X']],
            ])
            ->assertNotFound();
    }
}
