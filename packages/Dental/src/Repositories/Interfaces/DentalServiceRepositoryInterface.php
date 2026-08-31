<?php

namespace Packages\Dental\Src\Repositories\Interfaces;

use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;
use Packages\Dental\Src\Models\DentalService;

interface DentalServiceRepositoryInterface extends RepositoryInterface
{
    public function findPublishedBySlug(string $slug, int $facilityId, string $locale): ?DentalService;
}
