<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface MenuItemRepositoryInterface extends RepositoryInterface
{
    public function getTreeForMenu(int $menuId): Collection;
}
