<?php

namespace Packages\Catalog\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Catalog\Src\Models\Partner;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface PartnerRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug, string $locale): ?Partner;

    public function paginateActive(?string $type, ?int $destinationId, int $perPage = 15): LengthAwarePaginator;
}
