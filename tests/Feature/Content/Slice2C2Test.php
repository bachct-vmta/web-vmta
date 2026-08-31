<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Packages\Content\Database\Seeders\MenuSeeder;
use Packages\Content\Src\Jobs\IncrementPostViewCount;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Models\Post;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;
use Tests\TestCase;

class Slice2C2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Reset the cache buckets used by view-count dedup + sitemap caching so each test
        // sees a fresh slate even when phpunit.xml pins CACHE_STORE=array.
        Cache::flush();
    }

    /* ───────── Pageview queue job ───────── */

    public function test_post_show_dispatches_increment_job_instead_of_sync(): void
    {
        Queue::fake();

        $post = Post::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $post->translateOrNew('vi')->fill(['title' => 'Q', 'slug' => 'queue-post'])->save();

        $this->get('/vi/tin-tuc/queue-post')->assertOk();

        Queue::assertPushed(IncrementPostViewCount::class, function (IncrementPostViewCount $job) use ($post) {
            return $job->postId === $post->id;
        });
        // Critical: sync increment removed — view_count must remain 0 until queue runs.
        $this->assertSame(0, $post->fresh()->view_count);
    }

    public function test_increment_job_dedups_within_ttl_window_for_same_ip(): void
    {
        $post = Post::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $post->translateOrNew('vi')->fill(['title' => 'D', 'slug' => 'dedup-post'])->save();

        $job = new IncrementPostViewCount($post->id, '203.0.113.5');
        $job->handle(app(PostRepositoryInterface::class));
        $job->handle(app(PostRepositoryInterface::class));

        // First call increments; second call is silently de-duped by the cache lock.
        $this->assertSame(1, $post->fresh()->view_count);
    }

    public function test_increment_job_counts_different_ips_independently(): void
    {
        $post = Post::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $post->translateOrNew('vi')->fill(['title' => 'D2', 'slug' => 'dedup-2-post'])->save();

        $repo = app(PostRepositoryInterface::class);
        (new IncrementPostViewCount($post->id, '198.51.100.1'))->handle($repo);
        (new IncrementPostViewCount($post->id, '198.51.100.2'))->handle($repo);

        $this->assertSame(2, $post->fresh()->view_count);
    }

    /* ───────── Sitemap ───────── */

    public function test_sitemap_renders_xml_with_published_pages_and_posts(): void
    {
        $page = Page::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $page->translateOrNew('vi')->fill(['title' => 'Giới thiệu', 'slug' => 'gioi-thieu'])->save();
        $page->translateOrNew('en')->fill(['title' => 'About', 'slug' => 'about'])->save();

        $post = Post::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $post->translateOrNew('vi')->fill(['title' => 'P', 'slug' => 'bai-1'])->save();

        $r = $this->get('/sitemap.xml');

        $r->assertOk();
        $r->assertHeader('Content-Type', 'application/xml');
        $r->assertSee('<urlset', false);
        // Home + News list per locale.
        $r->assertSee('/vi</loc>', false);
        $r->assertSee('/vi/tin-tuc</loc>', false);
        // Page rows (both locales).
        $r->assertSee('/vi/gioi-thieu</loc>', false);
        $r->assertSee('/en/about</loc>', false);
        // Post.
        $r->assertSee('/vi/tin-tuc/bai-1</loc>', false);
        // hreflang alternate.
        $r->assertSee('hreflang="vi"', false);
    }

    public function test_sitemap_excludes_draft_content(): void
    {
        $draft = Post::create(['status' => 'draft']);
        $draft->translateOrNew('vi')->fill(['title' => 'Bài draft', 'slug' => 'bai-draft'])->save();

        $r = $this->get('/sitemap.xml');

        $r->assertOk();
        $r->assertDontSee('/bai-draft', false);
    }

    /* ───────── MenuSeeder ───────── */

    public function test_menu_seeder_creates_header_and_footer_menus(): void
    {
        $this->seed(MenuSeeder::class);

        $header = Menu::where('location', 'main_navigation')->first();
        $footer = Menu::where('location', 'footer_1_navigation')->first();

        $this->assertNotNull($header);
        $this->assertNotNull($footer);
        $this->assertSame(6, MenuItem::where('menu_id', $header->id)->whereNull('parent_id')->count());
        $this->assertSame(3, MenuItem::where('menu_id', $footer->id)->whereNull('parent_id')->count());
    }

    public function test_menu_seeder_is_idempotent(): void
    {
        $this->seed(MenuSeeder::class);
        $countAfterFirst = MenuItem::count();
        $this->seed(MenuSeeder::class);
        $this->assertSame($countAfterFirst, MenuItem::count(), 'Re-seeding must not duplicate items');
    }

    /* ───────── HTMLPurifier ───────── */

    public function test_purifier_strips_script_and_javascript_url_from_post_body(): void
    {
        $post = Post::create(['status' => 'published', 'published_at' => now()->subMinute()]);
        $post->translateOrNew('vi')->fill([
            'title' => 'XSS test',
            'slug' => 'xss-test',
            'body' => '<p>Safe <strong>bold</strong></p><script>alert(1)</script><a href="javascript:alert(2)">click</a>',
        ])->save();

        $r = $this->get('/vi/tin-tuc/xss-test');

        $r->assertOk();
        $r->assertSee('Safe', false);
        $r->assertSee('<strong>bold</strong>', false);
        // Hard fail: script tag + javascript: URL must not survive the purifier.
        $r->assertDontSee('<script>', false);
        $r->assertDontSee('javascript:alert', false);
    }

    /* ───────── Menu URL scheme guard ───────── */

    public function test_menu_partial_blocks_javascript_url(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'main_navigation', 'is_active' => true]);
        $item = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 1, 'is_active' => true,
        ]);
        $item->translateOrNew('vi')->fill([
            'label' => 'Evil',
            'url' => 'javascript:alert(1)',
        ])->save();

        $html = view('content::public.partials.menu', ['location' => 'main_navigation'])->render();

        $this->assertStringContainsString('Evil', $html);
        // href falls back to '#' when scheme is not whitelisted.
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('href="#"', $html);
    }

    public function test_menu_partial_allows_relative_and_http_schemes(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'main_navigation', 'is_active' => true]);
        $relative = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 1, 'is_active' => true,
        ]);
        $relative->translateOrNew('vi')->fill(['label' => 'Home', 'url' => '/vi'])->save();

        $external = MenuItem::create([
            'menu_id' => $menu->id, 'link_type' => 'url', 'sort_order' => 2, 'is_active' => true,
        ]);
        $external->translateOrNew('vi')->fill(['label' => 'External', 'url' => 'https://example.com'])->save();

        $html = view('content::public.partials.menu', ['location' => 'main_navigation'])->render();

        $this->assertStringContainsString('href="/vi"', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
    }
}
