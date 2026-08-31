<?php

namespace Packages\Catalog\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Catalog\Src\Models\Combo;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface ComboRepositoryInterface extends RepositoryInterface
{
    public function findPublishedBySlug(string $slug, string $locale): ?Combo;

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator;
}
