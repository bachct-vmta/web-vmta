<?php

namespace Packages\Dental\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface DentalCategoryRepositoryInterface extends RepositoryInterface
{
    public function publishedWithFacilities(string $locale): Collection;
}
