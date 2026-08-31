<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Models\Category;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Models\Post;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $overrides = [], string $slug = 'tin-1', string $locale = 'vi'): Post
    {
        $post = Post::create(array_merge([
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now()->subMinute(),
        ], $overrides));
        $post->translateOrNew($locale)->fill([
            'title' => $overrides['_title'] ?? 'Bài 1',
            'slug' => $slug,
            'excerpt' => 'Tóm tắt',
            'body' => '<p>Nội dung bài</p>',
        ])->save();

        return $post;
    }

    public function test_home_renders_latest_posts_via_news_teaser(): void
    {
        // Home now renders HomeSection content (see HomePageRenderTest). Latest posts still
        // surface via the news-teaser partial — verified here in isolation against an empty
        // home_sections table to confirm the fallback path keeps working.
        $this->makePost(['_title' => 'Bài tin tức 1'], 'home-bai-1');

        $r = $this->get('/vi');

        $r->assertOk();
        $r->assertSee('Bài tin tức 1');
    }

    public function test_home_works_when_data_is_empty(): void
    {
        $this->get('/vi')->assertOk();
    }

    public function test_post_index_lists_published_posts_only(): void
    {
        $this->makePost(['_title' => 'Bài published'], 'post-published');
        $draft = Post::create(['status' => 'draft']);
        $draft->translateOrNew('vi')->fill(['title' => 'Bài draft', 'slug' => 'post-draft'])->save();

        $r = $this->get('/vi/tin-tuc');

        $r->assertOk();
        $r->assertSee('Bài published');
        $r->assertDontSee('Bài draft');
    }

    public function test_post_show_renders_and_increments_view_count(): void
    {
        $post = $this->makePost(['_title' => 'Detail post'], 'detail-slug');
        $this->assertSame(0, $post->fresh()->view_count);

        $r = $this->get('/vi/tin-tuc/detail-slug');

        $r->assertOk();
        $r->assertSee('Detail post');
        $this->assertSame(1, $post->fresh()->view_count);
    }

    public function test_post_show_404_for_unknown_slug(): void
    {
        $this->get('/vi/tin-tuc/missing-post')->assertNotFound();
    }

    public function test_post_show_404_for_draft_post(): void
    {
        $draft = Post::create(['status' => 'draft']);
        $draft->translateOrNew('vi')->fill(['title' => 'Bài draft', 'slug' => 'draft-slug'])->save();

        $this->get('/vi/tin-tuc/draft-slug')->assertNotFound();
    }

    public function test_post_index_filters_by_category(): void
    {
        $cat = Category::create(['sort_order' => 0, 'is_active' => true]);
        $cat->translateOrNew('vi')->fill(['name' => 'Y tế', 'slug' => 'y-te'])->save();

        $this->makePost(['_title' => 'In category', 'category_id' => $cat->id], 'in-cat');
        $this->makePost(['_title' => 'Outside category'], 'out-cat');

        $r = $this->get('/vi/tin-tuc?category_id='.$cat->id);
        $r->assertOk();
        $r->assertSee('In category');
        $r->assertDontSee('Outside category');
    }

    public function test_page_show_renders_published_page(): void
    {
        $page = Page::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $page->translateOrNew('vi')->fill([
            'title' => 'Trang thông tin',
            'slug' => 'trang-thong-tin',
            'body' => '<p>Nội dung trang</p>',
        ])->save();

        $r = $this->get('/vi/trang-thong-tin');

        $r->assertOk();
        $r->assertSee('Trang thông tin');
        $r->assertSee('Nội dung trang', false);
    }

    public function test_page_show_404_for_draft(): void
    {
        $page = Page::create(['status' => 'draft']);
        $page->translateOrNew('vi')->fill(['title' => 'Draft page', 'slug' => 'draft-page'])->save();

        $this->get('/vi/draft-page')->assertNotFound();
    }

    public function test_menu_partial_renders_active_items(): void
    {
        // Build a header menu with one root item + one child via DB; render via the partial.
        $menu = Menu::create(['name' => 'Header', 'location' => 'main_navigation', 'is_active' => true]);
        $root = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 1, 'is_active' => true,
        ]);
        $root->translateOrNew('vi')->fill(['label' => 'Trang chủ', 'url' => '/vi'])->save();

        // Render via the home page (which extends layout that doesn't yet include the partial),
        // so we exercise the partial via direct view render to keep the test focused.
        $html = view('content::public.partials.menu', ['location' => 'main_navigation'])->render();

        $this->assertStringContainsString('Trang chủ', $html);
        $this->assertStringContainsString('Header', $html);
    }

    public function test_menu_partial_hides_inactive_items_and_children(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'main_navigation', 'is_active' => true]);
        $activeRoot = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 1, 'is_active' => true,
        ]);
        $activeRoot->translateOrNew('vi')->fill(['label' => 'ActiveRoot', 'url' => '/a'])->save();

        // Inactive root must NOT render.
        $inactiveRoot = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 2, 'is_active' => false,
        ]);
        $inactiveRoot->translateOrNew('vi')->fill(['label' => 'InactiveRoot', 'url' => '/x'])->save();

        // Inactive child of an active root must also NOT render.
        $inactiveChild = MenuItem::create([
            'menu_id' => $menu->id, 'parent_id' => $activeRoot->id, 'link_type' => 'url',
            'sort_order' => 0, 'is_active' => false,
        ]);
        $inactiveChild->translateOrNew('vi')->fill(['label' => 'InactiveChild', 'url' => '/c'])->save();

        // Active child must render.
        $activeChild = MenuItem::create([
            'menu_id' => $menu->id, 'parent_id' => $activeRoot->id, 'link_type' => 'url',
            'sort_order' => 1, 'is_active' => true,
        ]);
        $activeChild->translateOrNew('vi')->fill(['label' => 'ActiveChild', 'url' => '/c2'])->save();

        $html = view('content::public.partials.menu', ['location' => 'main_navigation'])->render();

        $this->assertStringContainsString('ActiveRoot', $html);
        $this->assertStringContainsString('ActiveChild', $html);
        $this->assertStringNotContainsString('InactiveRoot', $html);
        $this->assertStringNotContainsString('InactiveChild', $html);
    }

    public function test_post_listing_skips_posts_without_translation_for_current_locale(): void
    {
        // Post with VI translation only — should appear under /vi but NOT under /en.
        $viOnly = Post::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $viOnly->translateOrNew('vi')->fill(['title' => 'Chỉ tiếng Việt', 'slug' => 'chi-tieng-viet'])->save();

        $this->get('/vi/tin-tuc')->assertOk()->assertSee('Chỉ tiếng Việt');
        $this->get('/en/tin-tuc')->assertOk()->assertDontSee('Chỉ tiếng Việt');
    }

    public function test_home_renders_when_published_post_lacks_current_locale(): void
    {
        // Regression for H1: a post with no VI translation must not 500 the EN home, and vice versa.
        $enOnly = Post::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $enOnly->translateOrNew('en')->fill(['title' => 'EN only', 'slug' => 'en-only'])->save();

        $this->get('/vi')->assertOk()->assertDontSee('EN only');
        $this->get('/en')->assertOk()->assertSee('EN only');
    }
}
