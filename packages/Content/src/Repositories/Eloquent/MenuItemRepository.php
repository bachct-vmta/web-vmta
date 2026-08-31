<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Models\MenuItem;
use Packages\Content\Src\Repositories\Interfaces\MenuItemRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class MenuItemRepository extends BaseRepository implements MenuItemRepositoryInterface
{
    public function getModel(): string
    {
        return MenuItem::class;
    }

    public function getTreeForMenu(int $menuId): Collection
    {
        return $this->model
            ->with(['translations', 'children.translations'])
            ->where('menu_id', $menuId)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }
}
