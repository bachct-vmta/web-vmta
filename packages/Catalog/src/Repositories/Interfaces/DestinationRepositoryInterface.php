<?php

namespace Packages\Catalog\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\Destination;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface DestinationRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug, string $locale): ?Destination;

    public function allActive(): Collection;
}
