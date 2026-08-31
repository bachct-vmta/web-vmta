<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;

/**
 * Adds a "Chuyên khoa" child item under the existing "Y Tế – Trị Liệu" parent
 * in the main navigation, plus one child per active specialty (when present).
 *
 * Idempotent: re-runs upsert by (menu_id, parent_id, sort_order) instead of
 * cloning rows. Skips silently if the parent item isn't present (i.e. the
 * baseline MenuSeeder has not run yet).
 */
class MenuSpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $menu = Menu::where('location', 'main_navigation')->first();
        if (! $menu) {
            $this->command?->warn('main_navigation menu not found, run MenuSeeder first.');
            return;
        }

        $parent = MenuItem::where('menu_id', $menu->id)
            ->whereNull('parent_id')
            ->whereHas('translations', fn ($q) => $q->where('locale', 'vi')
                ->where(function ($q) {
                    $q->where('url', '/vi/y-te-tri-lieu')
                        ->orWhere('label', 'like', 'Y Tế%');
                }))
            ->first();

        if (! $parent) {
            $this->command?->warn('Y Tế – Trị Liệu parent menu item not found, skipping.');
            return;
        }

        DB::transaction(function () use ($menu, $parent) {
            $root = $this->upsertChild($menu->id, $parent->id, 1, [
                'vi' => ['label' => 'Chuyên khoa', 'url' => '/vi/chuyen-khoa'],
                'en' => ['label' => 'Specialties', 'url' => '/en/chuyen-khoa'],
            ]);

            $specialties = Specialty::with('translations')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            foreach ($specialties as $idx => $sp) {
                $vi = $sp->translations->firstWhere('locale', 'vi');
                $en = $sp->translations->firstWhere('locale', 'en') ?? $vi;
                if (! $vi?->slug) continue;

                $this->upsertChild($menu->id, $root->id, $idx + 1, [
                    'vi' => ['label' => $vi->name, 'url' => '/vi/chuyen-khoa/'.$vi->slug],
                    'en' => ['label' => $en?->name ?: $vi->name, 'url' => '/en/chuyen-khoa/'.($en?->slug ?: $vi->slug)],
                ]);
            }

            $this->command?->info('Menu items for Chuyên khoa seeded ('.$specialties->count().' children).');
        });
    }

    /** @param array<string, array{label:string,url:string}> $translations */
    private function upsertChild(int $menuId, ?int $parentId, int $sortOrder, array $translations): MenuItem
    {
        $existing = MenuItem::where('menu_id', $menuId)
            ->where('parent_id', $parentId)
            ->where('sort_order', $sortOrder)
            ->first();

        $item = $existing ?? new MenuItem;
        $item->menu_id = $menuId;
        $item->parent_id = $parentId;
        $item->link_type = 'url';
        $item->sort_order = $sortOrder;
        $item->is_active = true;
        $item->open_new_tab = false;
        $item->save();

        foreach ($translations as $locale => $payload) {
            $item->translateOrNew($locale)->fill([
                'label' => $payload['label'],
                'url' => $payload['url'],
            ])->save();
        }

        return $item;
    }
}
