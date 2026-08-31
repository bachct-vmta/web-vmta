<?php

namespace Packages\Content\Src\Services;

use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;

/**
 * Persists a nestable.serialize() tree into menu_items.
 * Walks recursively, applies parent_id + sort_order, all-or-nothing.
 */
class MenuItemReorderService
{
    public function __construct(private readonly MenuService $cache) {}

    /**
     * @param  array<int, array{id:int, children?: array}>  $tree
     */
    public function apply(int $menuId, array $tree): void
    {
        $updates = [];
        $this->flatten($tree, null, $updates);

        if ($updates === []) {
            return;
        }

        $ids = array_keys($updates);

        DB::transaction(function () use ($menuId, $ids, $updates) {
            $owned = MenuItem::query()
                ->whereIn('id', $ids)
                ->where('menu_id', $menuId)
                ->pluck('id')
                ->all();

            if (count($owned) !== count($ids)) {
                abort(422, __('content::content.menu.reorder.cross_menu'));
            }

            foreach ($updates as $id => $row) {
                MenuItem::query()
                    ->where('id', $id)
                    ->update([
                        'parent_id' => $row['parent_id'],
                        'sort_order' => $row['sort_order'],
                    ]);
            }

            $menu = Menu::find($menuId);
            if ($menu) {
                // afterCommit ensures we never bust cache while the write is
                // mid-transaction (would race against concurrent readers).
                DB::afterCommit(fn () => $this->cache->forgetMenu($menu->location));
            }
        });
    }

    /**
     * @param  array<int, array{id:int, children?: array}>  $nodes
     * @param  array<int, array{parent_id:?int, sort_order:int}>  $out
     */
    private function flatten(array $nodes, ?int $parentId, array &$out): void
    {
        foreach (array_values($nodes) as $index => $node) {
            if (! isset($node['id'])) {
                continue;
            }
            $id = (int) $node['id'];
            $out[$id] = [
                'parent_id' => $parentId,
                'sort_order' => $index,
            ];

            if (! empty($node['children']) && is_array($node['children'])) {
                $this->flatten($node['children'], $id, $out);
            }
        }
    }
}
