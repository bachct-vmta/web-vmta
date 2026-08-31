<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Repositories\Interfaces\CategoryRepositoryInterface;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_tree_returns_only_active_roots_with_children(): void
    {
        $repo = app(CategoryRepositoryInterface::class);

        $root = $repo->create([
            'is_active' => true,
            'sort_order' => 1,
            'vi' => ['name' => 'Tin tức', 'slug' => 'tin-tuc'],
            'en' => ['name' => 'News', 'slug' => 'news'],
        ]);

        $repo->create([
            'is_active' => true,
            'sort_order' => 1,
            'parent_id' => $root->id,
            'vi' => ['name' => 'Sự kiện', 'slug' => 'su-kien'],
            'en' => ['name' => 'Events', 'slug' => 'events'],
        ]);

        $repo->create([
            'is_active' => false,
            'sort_order' => 2,
            'vi' => ['name' => 'Ẩn', 'slug' => 'an'],
            'en' => ['name' => 'Hidden', 'slug' => 'hidden'],
        ]);

        $tree = $repo->tree();

        $this->assertCount(1, $tree);
        $this->assertCount(1, $tree->first()->children);
    }
}
