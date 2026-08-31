<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Models\Post;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function getModel(): string
    {
        return Post::class;
    }

    public function findBySlug(string $slug, string $locale): ?Post
    {
        return $this->model
            ->with(['translations', 'category.translations', 'author', 'coverMedia'])
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function findPublishedBySlug(string $slug, string $locale): ?Post
    {
        return $this->model
            ->with(['translations', 'category.translations', 'author', 'coverMedia'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function findPublishedByIds(array $ids, string $locale): Collection
    {
        if ($ids === []) {
            return new Collection();
        }

        $posts = $this->model
            ->with(['translations', 'category.translations', 'coverMedia'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereIn('id', $ids)
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale))
            ->get();

        // Preserve input order (most recently viewed first)
        $indexed = $posts->keyBy('id');

        return new Collection(array_values(array_filter(
            array_map(fn ($id) => $indexed->get($id), $ids)
        )));
    }

    public function paginatePublished(?int $categoryId = null, int $perPage = 12): LengthAwarePaginator
    {
        $locale = app()->getLocale();

        $query = $this->model
            ->with(['translations', 'category.translations', 'author', 'coverMedia'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            // Skip rows without a translation for the active locale so the listing view
            // never has to dereference a null translation (would 500 on $tr->slug).
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale));

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->latest('published_at')->paginate($perPage);
    }

    public function latest(int $limit = 5): Collection
    {
        $locale = app()->getLocale();

        return $this->model
            ->with(['translations', 'category.translations', 'author', 'coverMedia'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            // Match paginatePublished(): only return posts with a translation in the active locale.
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale))
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function incrementViewCount(int $postId): void
    {
        $this->model->newQuery()->where('id', $postId)->increment('view_count');
    }
}
