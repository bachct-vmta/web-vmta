<?php

namespace Packages\Catalog\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\TourPackage;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface TourPackageRepositoryInterface extends RepositoryInterface
{
    public function findPublishedBySlug(string $slug, string $locale): ?TourPackage;

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator;

    public function suggestRelated(?int $destinationId, int $limit = 5): Collection;
}
