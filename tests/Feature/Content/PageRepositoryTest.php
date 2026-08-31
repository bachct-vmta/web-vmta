<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Src\Repositories\Interfaces\PageRepositoryInterface;
use Tests\TestCase;

class PageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PageRepositoryInterface::class);
    }

    public function test_creates_page_with_vi_and_en_translations(): void
    {
        $page = $this->repository->create([
            'status' => 'published',
            'published_at' => now(),
            'vi' => [
                'title' => 'Giới thiệu VMTA',
                'slug' => 'gioi-thieu',
                'body' => 'Nội dung VI',
            ],
            'en' => [
                'title' => 'About VMTA',
                'slug' => 'about',
                'body' => 'EN content',
            ],
        ]);

        $this->assertSame('Giới thiệu VMTA', $page->translate('vi')->title);
        $this->assertSame('About VMTA', $page->translate('en')->title);
        $this->assertDatabaseCount('page_translations', 2);
    }

    public function test_find_by_slug_returns_correct_page_per_locale(): void
    {
        $this->repository->create([
            'status' => 'published',
            'vi' => ['title' => 'A', 'slug' => 'lien-he'],
            'en' => ['title' => 'A', 'slug' => 'contact'],
        ]);

        $byVi = $this->repository->findBySlug('lien-he', 'vi');
        $byEn = $this->repository->findBySlug('contact', 'en');
        $missing = $this->repository->findBySlug('contact', 'vi');

        $this->assertNotNull($byVi);
        $this->assertNotNull($byEn);
        $this->assertSame($byVi->id, $byEn->id);
        $this->assertNull($missing);
    }

    public function test_allows_same_slug_in_different_locales(): void
    {
        $this->repository->create([
            'status' => 'published',
            'vi' => ['title' => 'A', 'slug' => 'about'],
            'en' => ['title' => 'A', 'slug' => 'about'],
        ]);

        $this->assertDatabaseHas('page_translations', ['locale' => 'vi', 'slug' => 'about']);
        $this->assertDatabaseHas('page_translations', ['locale' => 'en', 'slug' => 'about']);
    }

    public function test_find_published_by_slug_excludes_drafts_and_future(): void
    {
        $this->repository->create([
            'status' => 'draft',
            'published_at' => now()->subDay(),
            'vi' => ['title' => 'Draft', 'slug' => 'draft-page'],
            'en' => ['title' => 'Draft', 'slug' => 'draft-page-en'],
        ]);

        $this->repository->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
            'vi' => ['title' => 'Future', 'slug' => 'future-page'],
            'en' => ['title' => 'Future', 'slug' => 'future-page-en'],
        ]);

        $live = $this->repository->create([
            'status' => 'published',
            'published_at' => now()->subHour(),
            'vi' => ['title' => 'Live', 'slug' => 'live-page'],
            'en' => ['title' => 'Live', 'slug' => 'live-page-en'],
        ]);

        $this->assertNull($this->repository->findPublishedBySlug('draft-page', 'vi'));
        $this->assertNull($this->repository->findPublishedBySlug('future-page', 'vi'));
        $this->assertNotNull($this->repository->findPublishedBySlug('live-page', 'vi'));
        $this->assertSame($live->id, $this->repository->findPublishedBySlug('live-page', 'vi')->id);
    }
}
