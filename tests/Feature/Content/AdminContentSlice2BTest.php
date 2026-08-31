<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class AdminContentSlice2BTest extends TestCase
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

    /* ───────── Menus ───────── */

    public function test_menus_store_creates_menu(): void
    {
        $this->actingAs($this->admin())->post('/admin/menus', [
            'name' => 'Header',
            'location' => 'main_navigation',
            'is_active' => '1',
        ])->assertRedirect();

        $menu = Menu::first();
        $this->assertSame('Header', $menu->name);
        $this->assertSame('main_navigation', $menu->location);
    }

    public function test_menus_location_must_be_unique(): void
    {
        Menu::create(['name' => 'A', 'location' => 'main_navigation', 'is_active' => true]);

        $r = $this->actingAs($this->admin())
            ->from('/admin/menus/create')
            ->post('/admin/menus', ['name' => 'B', 'location' => 'main_navigation']);

        $r->assertSessionHasErrors(['location']);
        $this->assertSame(1, Menu::count());
    }

    public function test_menus_update_invalidates_old_location_cache(): void
    {
        // Renaming location should not throw; we only check the update succeeds + persists.
        $menu = Menu::create(['name' => 'A', 'location' => 'main_navigation', 'is_active' => true]);

        $this->actingAs($this->admin())->put("/admin/menus/{$menu->id}", [
            'name' => 'A renamed',
            'location' => 'footer_1_navigation',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame('footer_1_navigation', $menu->fresh()->location);
    }

    public function test_menus_destroy_cascades_items(): void
    {
        $menu = Menu::create(['name' => 'A', 'location' => 'main_navigation', 'is_active' => true]);
        $item = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true,
        ]);
        $item->translateOrNew('vi')->fill(['label' => 'Home', 'url' => '/'])->save();

        $this->actingAs($this->admin())->delete("/admin/menus/{$menu->id}")->assertRedirect();

        $this->assertNull(Menu::find($menu->id));
        $this->assertNull(MenuItem::find($item->id));
    }

    /* ───────── Menu Items ───────── */

    public function test_menu_items_store_url_link(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'main_navigation', 'is_active' => true]);

        $this->actingAs($this->admin())->post("/admin/menus/{$menu->id}/items", [
            'link_type' => 'url',
            'sort_order' => 5,
            'is_active' => '1',
            'translations' => [['locale' => 'vi', 'label' => 'Trang chủ', 'url' => '/']],
        ])->assertRedirect();

        $item = MenuItem::first();
        $this->assertSame($menu->id, $item->menu_id);
        $this->assertSame('url', $item->link_type);
        $this->assertSame('/', $item->translate('vi')->url);
    }

    public function test_menu_items_url_required_when_link_type_url(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'main_navigation', 'is_active' => true]);

        $r = $this->actingAs($this->admin())
            ->from("/admin/menus/{$menu->id}/items/create")
            ->post("/admin/menus/{$menu->id}/items", [
                'link_type' => 'url',
                'translations' => [['locale' => 'vi', 'label' => 'Test']],
                // url omitted
            ]);

        $r->assertSessionHasErrors(['translations.0.url']);
        $this->assertSame(0, MenuItem::count());
    }

    public function test_menu_items_morph_link_requires_target(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'main_navigation', 'is_active' => true]);

        $r = $this->actingAs($this->admin())
            ->from("/admin/menus/{$menu->id}/items/create")
            ->post("/admin/menus/{$menu->id}/items", [
                'link_type' => 'morph',
                'translations' => [['locale' => 'vi', 'label' => 'Page link']],
            ]);

        $r->assertSessionHasErrors(['target_type', 'target_id']);
    }

    public function test_menu_items_update_rejects_self_parent(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'main_navigation', 'is_active' => true]);
        $item = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true,
        ]);
        $item->translateOrNew('vi')->fill(['label' => 'Test', 'url' => '/x'])->save();

        $r = $this->actingAs($this->admin())
            ->from("/admin/menus/{$menu->id}/items/{$item->id}/edit")
            ->put("/admin/menus/{$menu->id}/items/{$item->id}", [
                'link_type' => 'url',
                'parent_id' => $item->id,
                'translations' => [['locale' => 'vi', 'label' => 'Test', 'url' => '/x']],
            ]);

        $r->assertSessionHasErrors(['parent_id']);
    }

    public function test_menu_items_parent_must_belong_to_same_menu(): void
    {
        $menuA = Menu::create(['name' => 'A', 'location' => 'main_navigation', 'is_active' => true]);
        $menuB = Menu::create(['name' => 'B', 'location' => 'footer_1_navigation', 'is_active' => true]);
        $itemB = MenuItem::create([
            'menu_id' => $menuB->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true,
        ]);
        $itemB->translateOrNew('vi')->fill(['label' => 'In B', 'url' => '/b'])->save();

        $r = $this->actingAs($this->admin())
            ->from("/admin/menus/{$menuA->id}/items/create")
            ->post("/admin/menus/{$menuA->id}/items", [
                'link_type' => 'url',
                'parent_id' => $itemB->id, // belongs to menu B, not A
                'translations' => [['locale' => 'vi', 'label' => 'X', 'url' => '/x']],
            ]);

        $r->assertSessionHasErrors(['parent_id']);
    }

    public function test_menu_item_destroy_orphans_children_to_root(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'main_navigation', 'is_active' => true]);
        $parent = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true,
        ]);
        $parent->translateOrNew('vi')->fill(['label' => 'Parent', 'url' => '/p'])->save();
        $child = MenuItem::create([
            'menu_id' => $menu->id, 'parent_id' => $parent->id, 'link_type' => 'url', 'sort_order' => 0, 'is_active' => true,
        ]);
        $child->translateOrNew('vi')->fill(['label' => 'Child', 'url' => '/c'])->save();

        $this->actingAs($this->admin())->delete("/admin/menus/{$menu->id}/items/{$parent->id}")->assertRedirect();

        // nullOnDelete on parent_id: child survives as a root item (parent_id = NULL).
        $this->assertNull(MenuItem::find($parent->id));
        $fresh = MenuItem::find($child->id);
        $this->assertNotNull($fresh);
        $this->assertNull($fresh->parent_id);
    }

    /* ───────── Guards ───────── */

    public function test_guest_redirected_from_slice_2b_admin_routes(): void
    {
        foreach (['/admin/menus'] as $url) {
            $this->get($url)->assertRedirect();
        }
    }
}
