<?php

namespace Packages\Dental\Src\Repositories\Interfaces;

use Packages\Dental\Src\Models\DentalFacility;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface DentalFacilityRepositoryInterface extends RepositoryInterface
{
    public function findPublishedBySlug(string $slug, string $locale): ?DentalFacility;
}
