<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Repositories\Interfaces\CategoryRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\TagRepositoryInterface;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepositoryInterface $posts;

    private CategoryRepositoryInterface $categories;

    private TagRepositoryInterface $tags;

    protected function setUp(): void
    {
        parent::setUp();
        $this->posts = app(PostRepositoryInterface::class);
        $this->categories = app(CategoryRepositoryInterface::class);
        $this->tags = app(TagRepositoryInterface::class);
    }

    public function test_paginate_published_excludes_draft_and_future(): void
    {
        $this->makePost('Bài 1', 'bai-1', status: 'published', publishedAt: now()->subDay());
        $this->makePost('Bài 2', 'bai-2', status: 'draft');
        $this->makePost('Bài 3', 'bai-3', status: 'published', publishedAt: now()->addDay());

        $paginated = $this->posts->paginatePublished();

        $this->assertCount(1, $paginated->items());
    }

    public function test_filter_by_category(): void
    {
        $cat = $this->categories->create([
            'is_active' => true,
            'vi' => ['name' => 'Tin tức', 'slug' => 'tin-tuc'],
            'en' => ['name' => 'News', 'slug' => 'news'],
        ]);

        $this->makePost('A', 'a', status: 'published', publishedAt: now(), categoryId: $cat->id);
        $this->makePost('B', 'b', status: 'published', publishedAt: now());

        $filtered = $this->posts->paginatePublished(categoryId: $cat->id);

        $this->assertCount(1, $filtered->items());
    }

    public function test_increment_view_count_is_atomic(): void
    {
        $post = $this->makePost('A', 'a', status: 'published', publishedAt: now());

        $this->posts->incrementViewCount($post->id);
        $this->posts->incrementViewCount($post->id);

        $this->assertSame(2, (int) $post->fresh()->view_count);
    }

    public function test_paginate_published_avoids_n_plus_one(): void
    {
        $cat = $this->categories->create([
            'is_active' => true,
            'vi' => ['name' => 'C', 'slug' => 'c-vi'],
            'en' => ['name' => 'C', 'slug' => 'c-en'],
        ]);

        for ($i = 0; $i < 20; $i++) {
            $this->makePost("Bài {$i}", "bai-{$i}", status: 'published', publishedAt: now()->subMinute(), categoryId: $cat->id);
        }

        DB::enableQueryLog();
        $paginated = $this->posts->paginatePublished(perPage: 20);
        foreach ($paginated->items() as $post) {
            $post->translate('vi')->title;
            $post->category?->translate('vi')->name;
        }
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThan(
            10,
            count($queries),
            'Loading 20 posts with translations + category should fire <10 queries; got '.count($queries)
        );
    }

    private function makePost(string $title, string $slug, string $status = 'draft', $publishedAt = null, ?int $categoryId = null)
    {
        return $this->posts->create([
            'status' => $status,
            'category_id' => $categoryId,
            'published_at' => $publishedAt,
            'vi' => ['title' => $title, 'slug' => $slug.'-vi'],
            'en' => ['title' => $title, 'slug' => $slug.'-en'],
        ]);
    }
}
