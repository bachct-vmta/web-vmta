<?php

namespace Packages\Content\Src\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Content\Src\Models\Category;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Models\Post;

/**
 * Lazy search for entities that can be linked to from a menu.
 * Returns a uniform shape so the picker UI does not branch per type.
 */
class MenuSourceService
{
    public const ALLOWED_TYPES = ['page', 'post', 'category'];

    public const PER_PAGE = 15;

    /**
     * @return array{data: array<int, array<string, mixed>>, meta: array{current_page:int,last_page:int,per_page:int,total:int}}
     */
    public function search(string $type, string $query, int $page = 1, ?int $perPage = null): array
    {
        $perPage = $perPage ?: self::PER_PAGE;
        $locale = app()->getLocale();
        $q = trim($query);

        $paginator = match ($type) {
            'page' => $this->searchPages($q, $locale, $page, $perPage),
            'post' => $this->searchPosts($q, $locale, $page, $perPage),
            'category' => $this->searchCategories($q, $locale, $page, $perPage),
            default => null,
        };

        if ($paginator === null) {
            return $this->emptyResult($page, $perPage);
        }

        return [
            'data' => $paginator->getCollection()->map(fn ($row) => $this->mapRow($row, $type, $locale))->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    private function searchPages(string $q, string $locale, int $page, int $perPage): LengthAwarePaginator
    {
        return Page::query()
            ->with(['translations' => fn ($t) => $t->where('locale', $locale)])
            ->where('status', 'published')
            ->when($q !== '', fn ($query) => $query->whereHas('translations', fn ($t) => $t->where('locale', $locale)->where('title', 'like', "%$q%")))
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function searchPosts(string $q, string $locale, int $page, int $perPage): LengthAwarePaginator
    {
        return Post::query()
            ->with(['translations' => fn ($t) => $t->where('locale', $locale)])
            ->where('status', 'published')
            ->when($q !== '', fn ($query) => $query->whereHas('translations', fn ($t) => $t->where('locale', $locale)->where('title', 'like', "%$q%")))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function searchCategories(string $q, string $locale, int $page, int $perPage): LengthAwarePaginator
    {
        return Category::query()
            ->with(['translations' => fn ($t) => $t->where('locale', $locale)])
            ->where('is_active', true)
            ->when($q !== '', fn ($query) => $query->whereHas('translations', fn ($t) => $t->where('locale', $locale)->where('name', 'like', "%$q%")))
            ->orderBy('sort_order')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @return array{id:int, title:string, type:string, subtitle:?string}
     */
    private function mapRow(object $row, string $type, string $locale): array
    {
        $translation = $row->translations->first();
        if ($type === 'category') {
            $title = $translation?->name ?? '#'.$row->id;
            $slug = $translation?->slug;
        } else {
            $title = $translation?->title ?? '#'.$row->id;
            $slug = $translation?->slug;
        }

        return [
            'id' => (int) $row->id,
            'title' => (string) $title,
            'type' => $type,
            'subtitle' => $slug ? '/'.ltrim((string) $slug, '/') : null,
        ];
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, meta: array{current_page:int,last_page:int,per_page:int,total:int}}
     */
    private function emptyResult(int $page, int $perPage): array
    {
        return [
            'data' => [],
            'meta' => [
                'current_page' => $page,
                'last_page' => $page,
                'per_page' => $perPage,
                'total' => 0,
            ],
        ];
    }
}
