<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Models\AllianceSection;
use Packages\Content\Src\Repositories\Interfaces\AllianceSectionRepositoryInterface;
use Packages\Content\Src\Services\AlliancePageService;
use Tests\TestCase;

class AllianceSectionDataLayerTest extends TestCase
{
    use RefreshDatabase;

    private AllianceSectionRepositoryInterface $repo;

    private AlliancePageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(AllianceSectionRepositoryInterface::class);
        $this->service = app(AlliancePageService::class);
    }

    public function test_upserts_section_with_translations_vi_and_en(): void
    {
        $section = $this->repo->upsertSection(
            AllianceSectionPosition::Hero,
            [],
            [
                'vi' => ['title' => 'Mạng lưới Liên minh'],
                'en' => ['title' => 'Alliance Network'],
            ]
        );

        $this->assertSame('hero', $section->position->value);
        $this->assertSame('Mạng lưới Liên minh', $section->translate('vi')->title);
        $this->assertSame('Alliance Network', $section->translate('en')->title);
        $this->assertSame(10, $section->sort_order);
        $this->assertTrue($section->is_active);
    }

    public function test_upsert_is_idempotent(): void
    {
        $this->repo->upsertSection(AllianceSectionPosition::Hero, [], ['vi' => ['title' => 'X']]);
        $this->repo->upsertSection(AllianceSectionPosition::Hero, [], ['vi' => ['title' => 'Y']]);

        $this->assertSame(1, AllianceSection::query()->where('position', 'hero')->count());
        $this->assertSame('Y', AllianceSection::query()->where('position', 'hero')->first()->translate('vi')->title);
    }

    public function test_get_ordered_sections_filters_active_and_sorts(): void
    {
        $this->seedAll();
        AllianceSection::query()->where('position', 'map')->update(['is_active' => false]);

        $positions = $this->repo->getOrderedSections('vi')
            ->pluck('position')
            ->map(fn ($p) => $p->value)
            ->all();

        $this->assertSame(['hero', 'overview', 'standards', 'join_form'], $positions);
    }

    public function test_get_all_sections_with_all_translations_returns_5(): void
    {
        $this->seedAll();

        $sections = $this->repo->getAllSectionsWithAllTranslations();

        $this->assertCount(5, $sections);
        $this->assertSame('hero', $sections->first()->position->value);
        $this->assertSame('join_form', $sections->last()->position->value);
    }

    public function test_find_by_position_returns_section(): void
    {
        $this->repo->upsertSection(AllianceSectionPosition::Standards, [], ['vi' => ['title' => 'Tiêu chuẩn']]);

        $section = $this->repo->findByPosition(AllianceSectionPosition::Standards);

        $this->assertNotNull($section);
        $this->assertSame('Tiêu chuẩn', $section->translate('vi')->title);
    }

    public function test_validate_items_standards_requires_exactly_5(): void
    {
        $items = array_fill(0, 5, ['icon' => 'a.png', 'label' => 'L']);
        $this->service->validateItems(AllianceSectionPosition::Standards, $items);

        $this->assertTrue(true);
    }

    public function test_validate_items_standards_rejects_4(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateItems(AllianceSectionPosition::Standards, array_fill(0, 4, ['icon' => 'a.png', 'label' => 'L']));
    }

    public function test_validate_items_standards_rejects_6(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateItems(AllianceSectionPosition::Standards, array_fill(0, 6, ['icon' => 'a.png', 'label' => 'L']));
    }

    public function test_validate_items_hero_accepts_null(): void
    {
        $this->service->validateItems(AllianceSectionPosition::Hero, null);
        $this->assertTrue(true);
    }

    public function test_validate_items_hero_rejects_non_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateItems(AllianceSectionPosition::Hero, [['x' => 'y']]);
    }

    public function test_cache_invalidated_on_demand(): void
    {
        $this->seedAll();
        $this->service->getRenderData('vi');
        $this->assertTrue(Cache::has('content:alliance:render:vi'));

        $this->service->invalidateCache();
        $this->assertFalse(Cache::has('content:alliance:render:vi'));
    }

    public function test_default_sort_order_for_each_position(): void
    {
        $this->assertSame(10, AllianceSectionPosition::Hero->defaultSortOrder());
        $this->assertSame(20, AllianceSectionPosition::Overview->defaultSortOrder());
        $this->assertSame(30, AllianceSectionPosition::Standards->defaultSortOrder());
        $this->assertSame(40, AllianceSectionPosition::Map->defaultSortOrder());
        $this->assertSame(50, AllianceSectionPosition::JoinForm->defaultSortOrder());
    }

    private function seedAll(): void
    {
        foreach (AllianceSectionPosition::cases() as $p) {
            $this->repo->upsertSection($p, [], [
                'vi' => ['title' => $p->value.'-vi'],
                'en' => ['title' => $p->value.'-en'],
            ]);
        }
    }
}
