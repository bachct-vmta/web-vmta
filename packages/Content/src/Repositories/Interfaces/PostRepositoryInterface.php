<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Models\Post;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface PostRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug, string $locale): ?Post;

    public function findPublishedBySlug(string $slug, string $locale): ?Post;

    /**
     * Batch fetch published posts by IDs, preserving input order.
     *
     * @param  array<int, int>  $ids
     */
    public function findPublishedByIds(array $ids, string $locale): Collection;

    public function paginatePublished(?int $categoryId = null, int $perPage = 12): LengthAwarePaginator;

    public function latest(int $limit = 5): Collection;

    public function incrementViewCount(int $postId): void;
}
