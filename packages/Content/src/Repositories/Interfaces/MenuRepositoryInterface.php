<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Packages\Content\Src\Models\Menu;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface MenuRepositoryInterface extends RepositoryInterface
{
    public function findByLocation(string $location): ?Menu;
}
