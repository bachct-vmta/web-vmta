<?php

namespace Tests\Unit\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;
use Packages\Content\Src\Services\MenuItemReorderService;
use Tests\TestCase;

class MenuItemReorderServiceTest extends TestCase
{
    use RefreshDatabase;

    private MenuItemReorderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MenuItemReorderService::class);
    }

    private function makeMenuWithItems(int $count = 3): array
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'main_navigation', 'is_active' => true]);
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $items[] = MenuItem::create([
                'menu_id' => $menu->id,
                'link_type' => 'url',
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        return [$menu, $items];
    }

    public function test_apply_flat_tree_persists_sort_order(): void
    {
        [$menu, $items] = $this->makeMenuWithItems(3);
        [$a, $b, $c] = $items;

        $this->service->apply($menu->id, [
            ['id' => $c->id],
            ['id' => $a->id],
            ['id' => $b->id],
        ]);

        $this->assertSame(0, $c->fresh()->sort_order);
        $this->assertSame(1, $a->fresh()->sort_order);
        $this->assertSame(2, $b->fresh()->sort_order);
    }

    public function test_apply_nested_tree_sets_parent_id(): void
    {
        [$menu, $items] = $this->makeMenuWithItems(3);
        [$root, $childA, $childB] = $items;

        $this->service->apply($menu->id, [[
            'id' => $root->id,
            'children' => [
                ['id' => $childA->id],
                ['id' => $childB->id],
            ],
        ]]);

        $this->assertNull($root->fresh()->parent_id);
        $this->assertSame($root->id, $childA->fresh()->parent_id);
        $this->assertSame($root->id, $childB->fresh()->parent_id);
        $this->assertSame(0, $childA->fresh()->sort_order);
        $this->assertSame(1, $childB->fresh()->sort_order);
    }

    public function test_apply_three_level_tree_persists_correctly(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'main_navigation', 'is_active' => true]);
        $l1 = MenuItem::create(['menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true]);
        $l2 = MenuItem::create(['menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true]);
        $l3 = MenuItem::create(['menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true]);

        $this->service->apply($menu->id, [[
            'id' => $l1->id,
            'children' => [[
                'id' => $l2->id,
                'children' => [
                    ['id' => $l3->id],
                ],
            ]],
        ]]);

        $this->assertNull($l1->fresh()->parent_id);
        $this->assertSame($l1->id, $l2->fresh()->parent_id);
        $this->assertSame($l2->id, $l3->fresh()->parent_id);
    }

    public function test_apply_rejects_cross_menu_id(): void
    {
        [$menuA, $itemsA] = $this->makeMenuWithItems(1);
        $menuB = Menu::create(['name' => 'B', 'location' => 'footer_1_navigation', 'is_active' => true]);

        $this->expectExceptionMessageMatches('/menu/i');
        $this->service->apply($menuB->id, [['id' => $itemsA[0]->id]]);
    }
}
