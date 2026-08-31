<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Models\Category;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Models\Post;
use Packages\Content\Src\Models\Tag;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class AdminContentCrudTest extends TestCase
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

    /* ───────── Pages ───────── */

    public function test_pages_index_renders(): void
    {
        $page = Page::create(['status' => 'draft']);
        $page->translateOrNew('vi')->fill(['title' => 'About Us', 'slug' => 'about-us'])->save();

        $r = $this->actingAs($this->admin())->get('/admin/pages');
        $r->assertOk();
        $r->assertSee('About Us');
    }

    public function test_pages_store_creates_page_with_translations(): void
    {
        $r = $this->actingAs($this->admin())->post('/admin/pages', [
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'translations' => [
                ['locale' => 'vi', 'title' => 'Trang chủ', 'slug' => 'trang-chu', 'body' => '<p>Nội dung</p>'],
                ['locale' => 'en', 'title' => 'Home', 'slug' => 'home', 'body' => '<p>Content</p>'],
            ],
        ]);

        $r->assertRedirect('/admin/pages');
        $page = Page::with('translations')->first();
        $this->assertNotNull($page);
        $this->assertSame('published', $page->status);
        $this->assertSame('Trang chủ', $page->translate('vi')->title);
        $this->assertSame('Home', $page->translate('en')->title);
    }

    public function test_pages_update_modifies_translations_in_place(): void
    {
        $page = Page::create(['status' => 'draft']);
        $page->translateOrNew('vi')->fill(['title' => 'Old', 'slug' => 'old-slug'])->save();

        $this->actingAs($this->admin())->put("/admin/pages/{$page->id}", [
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'translations' => [['locale' => 'vi', 'title' => 'New', 'slug' => 'new-slug']],
        ])->assertRedirect();

        $this->assertSame('New', $page->fresh()->translate('vi')->title);
        $this->assertSame(1, $page->fresh()->translations()->count(), 'Should update in place, not duplicate');
    }

    public function test_pages_destroy_deletes(): void
    {
        $page = Page::create(['status' => 'draft']);
        $page->translateOrNew('vi')->fill(['title' => 'X', 'slug' => 'destroy-target'])->save();

        $this->actingAs($this->admin())->delete("/admin/pages/{$page->id}")->assertRedirect();

        $this->assertNull(Page::find($page->id));
    }

    public function test_pages_store_rejects_duplicate_slug_in_same_locale(): void
    {
        $existing = Page::create(['status' => 'draft']);
        $existing->translateOrNew('vi')->fill(['title' => 'A', 'slug' => 'shared-slug'])->save();

        $r = $this->actingAs($this->admin())
            ->from('/admin/pages/create')
            ->post('/admin/pages', [
                'status' => 'draft',
                'translations' => [['locale' => 'vi', 'title' => 'B', 'slug' => 'shared-slug']],
            ]);

        $r->assertSessionHasErrors(['translations.0.slug']);
        $this->assertSame(1, Page::count(), 'Duplicate slug must not create a second page');
    }

    public function test_pages_update_allows_keeping_own_slug(): void
    {
        $page = Page::create(['status' => 'draft']);
        $page->translateOrNew('vi')->fill(['title' => 'A', 'slug' => 'self-slug'])->save();

        $this->actingAs($this->admin())->put("/admin/pages/{$page->id}", [
            'status' => 'draft',
            'translations' => [['locale' => 'vi', 'title' => 'A renamed', 'slug' => 'self-slug']],
        ])->assertRedirect();

        $this->assertSame('A renamed', $page->fresh()->translate('vi')->title);
    }

    public function test_pages_published_without_date_is_rejected(): void
    {
        $r = $this->actingAs($this->admin())
            ->from('/admin/pages/create')
            ->post('/admin/pages', [
                'status' => 'published',
                // published_at intentionally omitted
                'translations' => [['locale' => 'vi', 'title' => 'P', 'slug' => 'published-no-date']],
            ]);

        $r->assertSessionHasErrors(['published_at']);
        $this->assertSame(0, Page::count());
    }

    public function test_pages_invalid_slug_rejected(): void
    {
        $r = $this->actingAs($this->admin())
            ->from('/admin/pages/create')
            ->post('/admin/pages', [
                'status' => 'draft',
                'translations' => [['locale' => 'vi', 'title' => 'X', 'slug' => 'Invalid Slug With Spaces']],
            ]);

        $r->assertSessionHasErrors(['translations.0.slug']);
        $this->assertSame(0, Page::count());
    }

    /* ───────── Posts ───────── */

    public function test_posts_store_with_category_and_tags(): void
    {
        $cat = Category::create(['sort_order' => 0, 'is_active' => true]);
        $cat->translateOrNew('vi')->fill(['name' => 'Tin', 'slug' => 'tin'])->save();
        $tag1 = Tag::create(['is_active' => true]);
        $tag1->translateOrNew('vi')->fill(['name' => 'Y tế', 'slug' => 'y-te'])->save();
        $tag2 = Tag::create(['is_active' => true]);
        $tag2->translateOrNew('vi')->fill(['name' => 'Du lịch', 'slug' => 'du-lich'])->save();

        $this->actingAs($this->admin())->post('/admin/posts', [
            'category_id' => $cat->id,
            'status' => 'published',
            'is_featured' => '1',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'tag_ids' => [$tag1->id, $tag2->id],
            'translations' => [['locale' => 'vi', 'title' => 'Bài 1', 'slug' => 'bai-1', 'body' => '<p>X</p>']],
        ])->assertRedirect('/admin/posts');

        $post = Post::with('tags')->first();
        $this->assertSame($cat->id, $post->category_id);
        $this->assertTrue($post->is_featured);
        $this->assertSame(2, $post->tags()->count());
    }

    public function test_posts_update_resyncs_tags(): void
    {
        $post = Post::create(['status' => 'draft']);
        $post->translateOrNew('vi')->fill(['title' => 'P', 'slug' => 'p'])->save();
        $t1 = Tag::create(['is_active' => true]);
        $t1->translateOrNew('vi')->fill(['name' => 't1', 'slug' => 't1'])->save();
        $t2 = Tag::create(['is_active' => true]);
        $t2->translateOrNew('vi')->fill(['name' => 't2', 'slug' => 't2'])->save();
        $post->tags()->attach($t1->id);

        $this->actingAs($this->admin())->put("/admin/posts/{$post->id}", [
            'status' => 'draft',
            'tag_ids' => [$t2->id],
            'translations' => [['locale' => 'vi', 'title' => 'P', 'slug' => 'p']],
        ])->assertRedirect();

        $this->assertSame([$t2->id], $post->fresh()->tags->pluck('id')->all());
    }

    /* ───────── Categories ───────── */

    public function test_categories_store_with_parent(): void
    {
        $parent = Category::create(['sort_order' => 0, 'is_active' => true]);
        $parent->translateOrNew('vi')->fill(['name' => 'Parent', 'slug' => 'parent'])->save();

        $this->actingAs($this->admin())->post('/admin/categories', [
            'parent_id' => $parent->id,
            'sort_order' => 5,
            'is_active' => '1',
            'translations' => [['locale' => 'vi', 'name' => 'Child', 'slug' => 'child']],
        ])->assertRedirect('/admin/categories');

        $child = Category::where('parent_id', $parent->id)->first();
        $this->assertNotNull($child);
        $this->assertSame(5, $child->sort_order);
    }

    public function test_categories_cannot_self_parent(): void
    {
        $cat = Category::create(['sort_order' => 0, 'is_active' => true]);
        $cat->translateOrNew('vi')->fill(['name' => 'X', 'slug' => 'x'])->save();

        $r = $this->actingAs($this->admin())
            ->from("/admin/categories/{$cat->id}/edit")
            ->put("/admin/categories/{$cat->id}", [
                'parent_id' => $cat->id,
                'translations' => [['locale' => 'vi', 'name' => 'X', 'slug' => 'x']],
            ]);

        $r->assertSessionHasErrors(['parent_id']);
    }

    /* ───────── Tags ───────── */

    public function test_tags_full_crud_cycle(): void
    {
        // Create
        $this->actingAs($this->admin())->post('/admin/tags', [
            'is_active' => '1',
            'translations' => [['locale' => 'vi', 'name' => 'Nha khoa', 'slug' => 'nha-khoa']],
        ])->assertRedirect('/admin/tags');
        $tag = Tag::first();
        $this->assertNotNull($tag);

        // Update
        $this->actingAs($this->admin())->put("/admin/tags/{$tag->id}", [
            'is_active' => '0',
            'translations' => [['locale' => 'vi', 'name' => 'Da liễu', 'slug' => 'da-lieu']],
        ])->assertRedirect();
        $this->assertSame('Da liễu', $tag->fresh()->translate('vi')->name);
        $this->assertFalse($tag->fresh()->is_active);

        // Delete
        $this->actingAs($this->admin())->delete("/admin/tags/{$tag->id}")->assertRedirect();
        $this->assertSame(0, Tag::count());
    }

    /* ───────── Guards ───────── */

    public function test_guest_redirected_from_admin_content_routes(): void
    {
        foreach (['/admin/pages', '/admin/posts', '/admin/categories', '/admin/tags'] as $url) {
            $this->get($url)->assertRedirect();
        }
    }
}
