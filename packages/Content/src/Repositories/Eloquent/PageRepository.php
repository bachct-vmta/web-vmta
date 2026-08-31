<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Repositories\Interfaces\PageRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public function getModel(): string
    {
        return Page::class;
    }

    public function findBySlug(string $slug, string $locale): ?Page
    {
        return $this->model
            ->with('translations')
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function findPublishedBySlug(string $slug, string $locale): ?Page
    {
        return $this->model
            ->with('translations')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with('translations')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate($perPage);
    }
}
