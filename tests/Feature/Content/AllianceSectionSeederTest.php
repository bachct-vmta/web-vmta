<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Database\Seeders\AllianceSectionSeeder;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Models\AllianceSection;
use Tests\TestCase;

class AllianceSectionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_5_alliance_sections(): void
    {
        $this->seed(AllianceSectionSeeder::class);

        $this->assertSame(5, AllianceSection::query()->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(AllianceSectionSeeder::class);
        $this->seed(AllianceSectionSeeder::class);

        $this->assertSame(5, AllianceSection::query()->count());
    }

    public function test_seeder_standards_section_has_5_items(): void
    {
        $this->seed(AllianceSectionSeeder::class);

        $section = AllianceSection::query()->where('position', 'standards')->first();
        $items = $section->translate('vi')->items;

        $this->assertIsArray($items);
        $this->assertCount(5, $items);
        $this->assertArrayHasKey('icon', $items[0]);
        $this->assertArrayHasKey('label', $items[0]);
        $this->assertArrayHasKey('description', $items[0]);
    }

    public function test_seeder_all_sections_active_with_correct_sort_order(): void
    {
        $this->seed(AllianceSectionSeeder::class);

        foreach (AllianceSectionPosition::cases() as $p) {
            $section = AllianceSection::query()->where('position', $p->value)->first();
            $this->assertNotNull($section, "missing position {$p->value}");
            $this->assertTrue($section->is_active);
            $this->assertSame($p->defaultSortOrder(), $section->sort_order);
        }
    }

    public function test_seeder_all_sections_have_vi_and_en_translations(): void
    {
        $this->seed(AllianceSectionSeeder::class);

        foreach (AllianceSectionPosition::cases() as $p) {
            $section = AllianceSection::query()->where('position', $p->value)->first();
            $this->assertNotNull($section->translate('vi')?->title);
            $this->assertNotNull($section->translate('en')?->title);
        }
    }
}
