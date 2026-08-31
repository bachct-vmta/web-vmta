<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Content\Src\Models\Page;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface PageRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug, string $locale): ?Page;

    public function findPublishedBySlug(string $slug, string $locale): ?Page;

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator;
}
