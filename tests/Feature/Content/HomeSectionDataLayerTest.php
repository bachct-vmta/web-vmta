<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Packages\Content\Database\Seeders\HomeSectionSeeder;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Models\HomeSection;
use Packages\Content\Src\Repositories\Interfaces\HomeSectionRepositoryInterface;
use Packages\Content\Src\Services\HomePageService;
use Tests\TestCase;

class HomeSectionDataLayerTest extends TestCase
{
    use RefreshDatabase;

    private HomeSectionRepositoryInterface $repo;

    private HomePageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(HomeSectionRepositoryInterface::class);
        $this->service = app(HomePageService::class);
    }

    public function test_repository_returns_8_active_sections_sorted_with_translation(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $sections = $this->repo->getOrderedSections('vi');

        $this->assertCount(8, $sections);
        $this->assertSame('hero', $sections->first()->position->value);
        $this->assertSame('why_vn', $sections->last()->position->value);
        $this->assertNotNull($sections->first()->translate('vi')?->title);
    }

    public function test_hero_section_has_marquee_items_with_label_value_keys(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $hero = HomeSection::query()->where('position', 'hero')->first();
        $items = $hero->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertArrayHasKey('label', $items[0]);
        $this->assertArrayHasKey('value', $items[0]);
    }

    public function test_hero_marquee_cardinality_between_4_and_10(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $hero = HomeSection::query()->where('position', 'hero')->first();
        $items = $hero->translate('vi')->items;

        $count = is_array($items) ? count($items) : 0;
        $range = HomeSectionPosition::Hero->expectedItemsRange();

        $this->assertNotNull($range);
        $this->assertGreaterThanOrEqual($range[0], $count);
        $this->assertLessThanOrEqual($range[1], $count);
    }

    public function test_values_section_exactly_3_items_with_icon_title_body(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $section = HomeSection::query()->where('position', 'values')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertArrayHasKey('icon', $items[0]);
        $this->assertArrayHasKey('title', $items[0]);
        $this->assertArrayHasKey('body', $items[0]);
    }

    public function test_about_section_exactly_3_bullets(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $section = HomeSection::query()->where('position', 'about')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertArrayHasKey('bullet', $items[0]);
    }

    public function test_solutions_section_exactly_3_icon_cards(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $section = HomeSection::query()->where('position', 'solutions')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertArrayHasKey('icon', $items[0]);
        $this->assertArrayHasKey('title', $items[0]);
        $this->assertArrayHasKey('body', $items[0]);
    }

    public function test_vision_mission_exactly_3_audience_items_and_has_video_url(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $section = HomeSection::query()->where('position', 'vision_mission')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertArrayHasKey('audience', $items[0]);
        $this->assertArrayHasKey('body', $items[0]);
        $this->assertNotNull($section->video_url);
        $this->assertStringContainsString('m3u8', $section->video_url);
    }

    public function test_benefits_section_exactly_2_rows_with_bullets_and_image_url(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $section = HomeSection::query()->where('position', 'benefits')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertArrayHasKey('audience', $items[0]);
        $this->assertArrayHasKey('bullets', $items[0]);
        $this->assertIsArray($items[0]['bullets']);
        $this->assertArrayHasKey('image_url', $items[0]);
    }

    public function test_technology_section_exactly_2_subgroups_with_bullets(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $section = HomeSection::query()->where('position', 'technology')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertArrayHasKey('title', $items[0]);
        $this->assertArrayHasKey('bullets', $items[0]);
        $this->assertIsArray($items[0]['bullets']);
    }

    public function test_why_vn_section_exactly_3_icon_cards(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $section = HomeSection::query()->where('position', 'why_vn')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(3, $items);
        $this->assertArrayHasKey('icon', $items[0]);
        $this->assertArrayHasKey('title', $items[0]);
        $this->assertArrayHasKey('body', $items[0]);
    }

    public function test_validate_items_rejects_wrong_cardinality_for_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateItems(HomeSectionPosition::Values, [
            ['icon' => 'a.png', 'title' => 'T', 'body' => 'B'],
            ['icon' => 'b.png', 'title' => 'T', 'body' => 'B'],
        ]);
    }

    public function test_validate_items_accepts_marquee_4_items(): void
    {
        // 4 items is within [4,10] — should not throw
        $this->service->validateItems(HomeSectionPosition::Hero, [
            ['label' => 'USA',      'value' => '10 000'],
            ['label' => 'Cambodia', 'value' => '10 000'],
            ['label' => 'France',   'value' => '10 000'],
            ['label' => 'Germany',  'value' => '10 000'],
        ]);

        $this->assertTrue(true);
    }

    public function test_validate_items_rejects_marquee_3_items(): void
    {
        // 3 items is below min=4 — should throw
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateItems(HomeSectionPosition::Hero, [
            ['label' => 'USA',      'value' => '10 000'],
            ['label' => 'Cambodia', 'value' => '10 000'],
            ['label' => 'France',   'value' => '10 000'],
        ]);
    }

    public function test_cache_invalidated_on_upsert(): void
    {
        $this->seed(HomeSectionSeeder::class);

        // Warm cache
        $this->service->getRenderData('vi');
        $this->assertTrue(Cache::has('content:home:render:vi'));

        // Invalidate
        $this->service->invalidateCache();
        $this->assertFalse(Cache::has('content:home:render:vi'));
    }

    public function test_inactive_section_excluded(): void
    {
        $this->seed(HomeSectionSeeder::class);

        HomeSection::query()->where('position', 'technology')->update(['is_active' => false]);

        $positions = $this->repo
            ->getOrderedSections('vi')
            ->pluck('position')
            ->map(fn ($p) => $p->value)
            ->all();

        $this->assertNotContains('technology', $positions);
        $this->assertCount(7, $positions);
    }
}
