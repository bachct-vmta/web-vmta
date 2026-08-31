<?php

namespace Packages\Content\Src\Services;

use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;

/**
 * Creates a new MenuItem at the root level (top of the tree), then attaches translations.
 * Used by the add-link panel (page / post / category picker + custom URL form).
 */
class MenuItemCreateService
{
    public function __construct(private readonly MenuService $cache) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function quickAdd(int $menuId, array $data): MenuItem
    {
        $menu = Menu::findOrFail($menuId);

        return DB::transaction(function () use ($menu, $data) {
            $maxSort = (int) (MenuItem::query()
                ->where('menu_id', $menu->id)
                ->whereNull('parent_id')
                ->max('sort_order') ?? -1);

            $item = MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => null,
                'link_type' => $data['link_type'],
                'target_type' => $data['target_type'] ?? null,
                'target_id' => $data['target_id'] ?? null,
                'icon' => $data['icon'] ?? null,
                'css_class' => null,
                'open_new_tab' => (bool) ($data['open_new_tab'] ?? false),
                'sort_order' => $maxSort + 1,
                'is_active' => true,
            ]);

            foreach ($data['translations'] as $row) {
                $item->translateOrNew($row['locale'])->fill([
                    'label' => $row['label'],
                    'url' => $row['url'] ?? null,
                ])->save();
            }

            DB::afterCommit(fn () => $this->cache->forgetMenu($menu->location));

            return $item->fresh(['translations']);
        });
    }
}
