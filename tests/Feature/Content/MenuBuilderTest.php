<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;
use Packages\Content\Src\Models\Page;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class MenuBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@nguyenkhoi.dev')->first();
    }

    private function makeMenu(string $location = 'main_navigation'): Menu
    {
        return Menu::create(['name' => 'M', 'location' => $location, 'is_active' => true]);
    }

    private function makeItem(Menu $menu, ?int $parentId = null, string $label = 'Item', int $sort = 0): MenuItem
    {
        $item = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'link_type' => 'url',
            'sort_order' => $sort,
            'is_active' => true,
        ]);
        $item->translateOrNew('vi')->fill(['label' => $label, 'url' => '/'.strtolower($label)])->save();
        $item->translateOrNew('en')->fill(['label' => $label, 'url' => '/en/'.strtolower($label)])->save();

        return $item->refresh();
    }

    /* ─────────── Reorder ─────────── */

    public function test_reorder_persists_flat_tree(): void
    {
        $menu = $this->makeMenu();
        $a = $this->makeItem($menu, null, 'A', 0);
        $b = $this->makeItem($menu, null, 'B', 1);
        $c = $this->makeItem($menu, null, 'C', 2);

        $this->actingAs($this->admin())
            ->postJson("/admin/menus/{$menu->id}/reorder", [
                'tree' => [
                    ['id' => $c->id],
                    ['id' => $a->id],
                    ['id' => $b->id],
                ],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(0, $c->fresh()->sort_order);
        $this->assertSame(1, $a->fresh()->sort_order);
        $this->assertSame(2, $b->fresh()->sort_order);
        $this->assertNull($a->fresh()->parent_id);
    }

    public function test_reorder_nests_items(): void
    {
        $menu = $this->makeMenu();
        $root = $this->makeItem($menu, null, 'Root', 0);
        $child1 = $this->makeItem($menu, null, 'Child1', 1);
        $child2 = $this->makeItem($menu, null, 'Child2', 2);

        $this->actingAs($this->admin())
            ->postJson("/admin/menus/{$menu->id}/reorder", [
                'tree' => [[
                    'id' => $root->id,
                    'children' => [
                        ['id' => $child1->id],
                        ['id' => $child2->id],
                    ],
                ]],
            ])
            ->assertOk();

        $this->assertSame($root->id, $child1->fresh()->parent_id);
        $this->assertSame($root->id, $child2->fresh()->parent_id);
        $this->assertSame(0, $child1->fresh()->sort_order);
        $this->assertSame(1, $child2->fresh()->sort_order);
    }

    public function test_reorder_rejects_depth_over_three(): void
    {
        $menu = $this->makeMenu();
        $items = [];
        for ($i = 0; $i < 4; $i++) {
            $items[$i] = $this->makeItem($menu, null, "I$i", $i);
        }

        // Build a 4-level deep tree (depth > MAX_DEPTH=3).
        $tree = [[
            'id' => $items[0]->id,
            'children' => [[
                'id' => $items[1]->id,
                'children' => [[
                    'id' => $items[2]->id,
                    'children' => [['id' => $items[3]->id]],
                ]],
            ]],
        ]];

        $this->actingAs($this->admin())
            ->postJson("/admin/menus/{$menu->id}/reorder", ['tree' => $tree])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tree']);
    }

    public function test_reorder_rejects_cross_menu_id(): void
    {
        $menuA = $this->makeMenu('main_navigation');
        $menuB = $this->makeMenu('footer_1_navigation');
        $itemA = $this->makeItem($menuA, null, 'A');

        $this->actingAs($this->admin())
            ->postJson("/admin/menus/{$menuB->id}/reorder", [
                'tree' => [['id' => $itemA->id]],
            ])
            ->assertStatus(422);
    }

    public function test_reorder_rejects_over_max_items(): void
    {
        $menu = $this->makeMenu();
        // Use fake ids that don't need to exist — the size check happens before DB lookup.
        $tree = array_map(fn ($i) => ['id' => $i], range(1, 201));

        $this->actingAs($this->admin())
            ->postJson("/admin/menus/{$menu->id}/reorder", ['tree' => $tree])
            ->assertStatus(422);
    }

    public function test_reorder_requires_content_edit_permission(): void
    {
        $menu = $this->makeMenu();
        $user = User::factory()->create(); // no admin role

        $this->actingAs($user)
            ->postJson("/admin/menus/{$menu->id}/reorder", [
                'tree' => [['id' => 1]],
            ])
            ->assertForbidden();
    }

    /* ─────────── Inline edit ─────────── */

    public function test_inline_edit_updates_scalar_fields(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, null, 'X');

        $this->actingAs($this->admin())
            ->patchJson("/admin/menu-items/{$item->id}", [
                'icon' => 'home',
                'open_new_tab' => true,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $fresh = $item->fresh();
        $this->assertSame('home', $fresh->icon);
        $this->assertTrue($fresh->open_new_tab);
    }

    public function test_inline_edit_upserts_translation(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, null, 'X');

        $this->actingAs($this->admin())
            ->patchJson("/admin/menu-items/{$item->id}", [
                'translations' => [
                    ['locale' => 'vi', 'label' => 'Trang chủ', 'url' => 'https://example.com'],
                ],
            ])
            ->assertOk();

        $tr = $item->fresh()->translations->firstWhere('locale', 'vi');
        $this->assertSame('Trang chủ', $tr->label);
        $this->assertSame('https://example.com', $tr->url);
    }

    public function test_inline_edit_prunes_empty_translation(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, null, 'X');

        $this->assertNotNull($item->translations->firstWhere('locale', 'en'));

        $this->actingAs($this->admin())
            ->patchJson("/admin/menu-items/{$item->id}", [
                'translations' => [
                    ['locale' => 'en', 'label' => '', 'url' => ''],
                ],
            ])
            ->assertOk();

        $this->assertNull($item->fresh()->translations->firstWhere('locale', 'en'));
        $this->assertNotNull($item->fresh()->translations->firstWhere('locale', 'vi'));
    }

    public function test_inline_edit_rejects_javascript_scheme(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, null, 'X');

        $this->actingAs($this->admin())
            ->patchJson("/admin/menu-items/{$item->id}", [
                'translations' => [
                    ['locale' => 'vi', 'label' => 'Bad', 'url' => 'javascript:alert(1)'],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['translations.0.url']);
    }

    public function test_inline_edit_enforces_link_type_target_consistency(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, null, 'X');

        $this->actingAs($this->admin())
            ->patchJson("/admin/menu-items/{$item->id}", [
                'link_type' => 'morph',
                'target_type' => 'page',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['target_id']);
    }

    public function test_inline_edit_rejects_unwhitelisted_target_type(): void
    {
        $menu = $this->makeMenu();
        $item = $this->makeItem($menu, null, 'X');

        $this->actingAs($this->admin())
            ->patchJson("/admin/menu-items/{$item->id}", [
                'link_type' => 'morph',
                'target_type' => 'User',
                'target_id' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['target_type']);
    }

    /* ─────────── Quick-add ─────────── */

    public function test_quick_add_creates_root_item_with_translation(): void
    {
        $menu = $this->makeMenu();
        $existing = $this->makeItem($menu, null, 'Existing', 3);

        $payload = [
            'link_type' => 'url',
            'translations' => [
                ['locale' => 'vi', 'label' => 'Trang mới', 'url' => 'https://example.com'],
                ['locale' => 'en', 'label' => 'New page', 'url' => 'https://example.com'],
            ],
        ];

        $response = $this->actingAs($this->admin())
            ->postJson("/admin/menus/{$menu->id}/items/quick-add", $payload)
            ->assertStatus(201)
            ->assertJson(['ok' => true]);

        $newId = $response->json('item.id');
        $new = MenuItem::find($newId);
        $this->assertNotNull($new);
        $this->assertGreaterThan($existing->sort_order, $new->sort_order);
        $this->assertSame('Trang mới', $new->translate('vi')->label);
    }

    public function test_quick_add_url_rejects_invalid_scheme(): void
    {
        $menu = $this->makeMenu();

        $this->actingAs($this->admin())
            ->postJson("/admin/menus/{$menu->id}/items/quick-add", [
                'link_type' => 'url',
                'translations' => [
                    ['locale' => 'vi', 'label' => 'Bad', 'url' => 'javascript:alert(1)'],
                ],
            ])
            ->assertStatus(422);
    }

    /* ─────────── Sources picker ─────────── */

    public function test_sources_returns_paginated_pages_for_locale(): void
    {
        $page = Page::create(['status' => 'published']);
        $page->translateOrNew('vi')->fill(['title' => 'About us', 'slug' => 'about-us', 'body' => 'b'])->save();

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/menus/sources?type=page&q=About')
            ->assertOk();

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertSame('About us', $data[0]['title']);
        $this->assertSame('page', $data[0]['type']);
    }

    public function test_sources_excludes_unpublished_pages(): void
    {
        $draft = Page::create(['status' => 'draft']);
        $draft->translateOrNew('vi')->fill(['title' => 'Hidden', 'slug' => 'hidden', 'body' => 'b'])->save();

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/menus/sources?type=page&q=Hidden')
            ->assertOk();

        $this->assertEmpty($response->json('data'));
    }

    /* ─────────── Destroy (AJAX) ─────────── */

    public function test_destroy_ajax_returns_json_and_promotes_children(): void
    {
        $menu = $this->makeMenu();
        $parent = $this->makeItem($menu, null, 'Parent');
        $child = $this->makeItem($menu, $parent->id, 'Child');

        $this->actingAs($this->admin())
            ->deleteJson("/admin/menu-items/{$parent->id}")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertNull(MenuItem::find($parent->id));
        // FK nullOnDelete: child survives as root.
        $this->assertNotNull(MenuItem::find($child->id));
        $this->assertNull($child->fresh()->parent_id);
    }

    /* ─────────── Location enum ─────────── */

    public function test_store_menu_rejects_arbitrary_location(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/menus/create')
            ->post('/admin/menus', [
                'name' => 'Bogus',
                'location' => 'wat',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['location']);

        $this->assertSame(0, Menu::count());
    }

    public function test_store_menu_accepts_each_whitelisted_location(): void
    {
        $allowed = array_keys(Menu::LOCATIONS);

        foreach ($allowed as $slug) {
            $this->actingAs($this->admin())
                ->post('/admin/menus', [
                    'name' => 'M-'.$slug,
                    'location' => $slug,
                    'is_active' => '1',
                ])
                ->assertRedirect();
        }

        $this->assertSame(count($allowed), Menu::count());
    }

    public function test_legacy_form_routes_still_work(): void
    {
        $menu = $this->makeMenu();

        $this->actingAs($this->admin())
            ->post("/admin/menus/{$menu->id}/items", [
                'link_type' => 'url',
                'translations' => [
                    ['locale' => 'vi', 'label' => 'Trang chủ', 'url' => '/'],
                    ['locale' => 'en', 'label' => 'Home', 'url' => '/en'],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(1, MenuItem::count());
    }
}
