<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Models\Category;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug, string $locale): ?Category;

    public function tree(): Collection;
}
