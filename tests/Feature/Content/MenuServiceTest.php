<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Packages\Content\Src\Services\MenuService;
use Tests\TestCase;

class MenuServiceTest extends TestCase
{
    use RefreshDatabase;

    private MenuService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MenuService::class);
    }

    public function test_fetch_menu_by_location_uses_cache(): void
    {
        $menu = $this->service->createMenu([
            'name' => 'Header',
            'location' => 'main_navigation',
            'is_active' => true,
        ]);

        $this->service->createItem([
            'menu_id' => $menu->id,
            'sort_order' => 1,
            'is_active' => true,
            'vi' => ['label' => 'Trang chủ', 'url' => '/vi'],
            'en' => ['label' => 'Home', 'url' => '/en'],
        ]);

        $first = $this->service->getMenu('main_navigation', 'vi');
        $this->assertNotNull($first);
        $this->assertTrue(Cache::has('content:menu:main_navigation:vi'));

        $second = $this->service->getMenu('main_navigation', 'vi');
        $this->assertSame($first->id, $second->id);
    }

    public function test_updating_menu_invalidates_cache(): void
    {
        $menu = $this->service->createMenu([
            'name' => 'Footer',
            'location' => 'footer_1_navigation',
            'is_active' => true,
        ]);

        $this->service->getMenu('footer_1_navigation', 'vi');
        $this->assertTrue(Cache::has('content:menu:footer_1_navigation:vi'));

        $this->service->updateMenu($menu->id, ['name' => 'Footer 2']);

        $this->assertFalse(Cache::has('content:menu:footer_1_navigation:vi'));
    }

    public function test_adding_item_invalidates_cache(): void
    {
        $menu = $this->service->createMenu([
            'name' => 'Mega',
            'location' => 'page_sidebar',
            'is_active' => true,
        ]);

        $this->service->getMenu('page_sidebar', 'vi');
        $this->assertTrue(Cache::has('content:menu:page_sidebar:vi'));

        $this->service->createItem([
            'menu_id' => $menu->id,
            'sort_order' => 1,
            'is_active' => true,
            'vi' => ['label' => 'Khoa', 'url' => '/vi/khoa'],
            'en' => ['label' => 'Dept', 'url' => '/en/dept'],
        ]);

        $this->assertFalse(Cache::has('content:menu:page_sidebar:vi'));
    }
}
