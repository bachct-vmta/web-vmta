<?php

namespace Packages\Content\Src\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Repositories\Interfaces\MenuItemRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\MenuRepositoryInterface;

/**
 * MenuService — fetch menus by location with cache.
 * Cache key namespaced by locale so VI/EN render independently.
 * Bust cache on save/delete via service-level invalidation.
 */
class MenuService
{
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        private MenuRepositoryInterface $menus,
        private MenuItemRepositoryInterface $items,
    ) {}

    public function getMenu(string $location, ?string $locale = null): ?Menu
    {
        $locale ??= app()->getLocale();
        $key = $this->cacheKey($location, $locale);

        return Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($location) {
            return $this->menus->findByLocation($location);
        });
    }

    public function createMenu(array $data): Menu
    {
        return DB::transaction(function () use ($data) {
            /** @var Menu $menu */
            $menu = $this->menus->create($data);
            $this->forgetMenu($menu->location);

            return $menu;
        });
    }

    public function updateMenu(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $existing = $this->menus->findOrFail($id);
            $ok = $this->menus->update($id, $data);
            $this->forgetMenu($existing->location);
            if (isset($data['location']) && $data['location'] !== $existing->location) {
                $this->forgetMenu($data['location']);
            }

            return $ok;
        });
    }

    public function deleteMenu(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $menu = $this->menus->findOrFail($id);
            $location = $menu->location;
            $ok = $this->menus->delete($id);
            $this->forgetMenu($location);

            return $ok;
        });
    }

    public function createItem(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $item = $this->items->create($data);
            $this->forgetMenuById((int) $data['menu_id']);

            return $item;
        });
    }

    public function updateItem(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $item = $this->items->findOrFail($id);
            $ok = $this->items->update($id, $data);
            $this->forgetMenuById((int) $item->menu_id);

            return $ok;
        });
    }

    public function deleteItem(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $item = $this->items->findOrFail($id);
            $menuId = (int) $item->menu_id;
            $ok = $this->items->delete($id);
            $this->forgetMenuById($menuId);

            return $ok;
        });
    }

    public function forgetMenu(string $location): void
    {
        foreach ($this->locales() as $locale) {
            Cache::forget($this->cacheKey($location, $locale));
        }
    }

    private function forgetMenuById(int $menuId): void
    {
        $menu = $this->menus->find($menuId);
        if ($menu) {
            $this->forgetMenu($menu->location);
        }
    }

    private function cacheKey(string $location, string $locale): string
    {
        return "content:menu:{$location}:{$locale}";
    }

    /**
     * @return array<int, string>
     */
    private function locales(): array
    {
        $configured = config('translatable.locales', ['vi', 'en']);
        $flat = [];
        foreach ($configured as $key => $value) {
            $flat[] = is_array($value) ? (string) $key : (string) $value;
        }

        return array_values(array_unique($flat));
    }
}
