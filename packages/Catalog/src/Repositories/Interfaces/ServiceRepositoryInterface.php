<?php

namespace Packages\Catalog\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\Service;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface ServiceRepositoryInterface extends RepositoryInterface
{
    public function findPublishedBySlug(string $slug, string $locale): ?Service;

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator;

    public function suggestRelated(?int $specialtyId, ?int $destinationId, int $limit = 5): Collection;
}
