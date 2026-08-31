<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Repositories\Interfaces\MenuRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{
    public function getModel(): string
    {
        return Menu::class;
    }

    public function findByLocation(string $location): ?Menu
    {
        // Recursive `is_active` filter at the eager-load level — public-side render must NEVER
        // emit inactive items (or their inactive children). Sort_order respected at both depths.
        return $this->model
            ->with(['rootItems' => fn ($q) => $q->where('is_active', true)
                ->orderBy('sort_order')
                ->with([
                    'translations',
                    'children' => fn ($c) => $c->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with('translations'),
                ]),
            ])
            ->where('location', $location)
            ->where('is_active', true)
            ->first();
    }
}
