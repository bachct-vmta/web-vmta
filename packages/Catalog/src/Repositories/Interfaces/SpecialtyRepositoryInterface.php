<?php

namespace Packages\Catalog\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface SpecialtyRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug, string $locale): ?Specialty;

    public function findPublishedBySlug(string $slug, string $locale): ?Specialty;

    public function allActive(): Collection;
}
