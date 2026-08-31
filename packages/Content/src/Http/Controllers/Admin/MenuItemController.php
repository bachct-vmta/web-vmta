<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Content\Src\Http\Requests\Admin\StoreMenuItemRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdateMenuItemAjaxRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdateMenuItemRequest;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;
use Packages\Content\Src\Services\MenuItemUpdateService;
use Packages\Content\Src\Services\MenuService;
use Packages\Core\Src\Http\Controllers\BaseController;

class MenuItemController extends BaseController
{
    public function __construct(
        private readonly MenuService $service,
        private readonly MenuItemUpdateService $updates,
    ) {}

    public function create(int $menu): View
    {
        $menuModel = Menu::with('items.translations')->findOrFail($menu);

        return view('content::admin.menu-items.form', [
            'menu' => $menuModel,
            'item' => new MenuItem(['link_type' => 'url', 'is_active' => true, 'open_new_tab' => false, 'sort_order' => 0]),
            // Cap parent picker at root items only — `_row.blade.php` renders depth 0 + 1; picking
            // a child as parent would create invisible grandchildren. Deeper hierarchy is Slice 2C UX.
            'parentOptions' => $menuModel->items->whereNull('parent_id')->values(),
            'mode' => 'create',
        ]);
    }

    public function store(StoreMenuItemRequest $request, int $menu): RedirectResponse
    {
        $data = $request->validated();
        $menuModel = Menu::findOrFail($menu);

        DB::transaction(function () use ($data, $menuModel) {
            $item = MenuItem::create([
                'menu_id' => $menuModel->id,
                'parent_id' => $data['parent_id'] ?? null,
                'link_type' => $data['link_type'],
                'target_type' => $data['target_type'] ?? null,
                'target_id' => $data['target_id'] ?? null,
                'icon' => $data['icon'] ?? null,
                'css_class' => $data['css_class'] ?? null,
                'open_new_tab' => (bool) ($data['open_new_tab'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($item, $data['translations']);
        });

        $this->service->forgetMenu($menuModel->location);

        return $this->redirectWithSuccess(admin_route_name('menus.edit'), __('content::content.menu_item.created'), ['menu' => $menuModel->id]);
    }

    public function edit(int $menu, int $item): View
    {
        $menuModel = Menu::with('items.translations')->findOrFail($menu);
        $itemModel = MenuItem::with('translations')->where('menu_id', $menuModel->id)->findOrFail($item);

        return view('content::admin.menu-items.form', [
            'menu' => $menuModel,
            'item' => $itemModel,
            // Exclude self + cap at root items only (same rationale as create()).
            'parentOptions' => $menuModel->items->whereNull('parent_id')->where('id', '!=', $itemModel->id)->values(),
            'mode' => 'edit',
        ]);
    }

    public function update(UpdateMenuItemRequest $request, int $menu, int $item): RedirectResponse
    {
        $data = $request->validated();
        $menuModel = Menu::findOrFail($menu);
        $itemModel = MenuItem::where('menu_id', $menuModel->id)->findOrFail($item);

        DB::transaction(function () use ($itemModel, $data) {
            $itemModel->update([
                'parent_id' => $data['parent_id'] ?? null,
                'link_type' => $data['link_type'],
                'target_type' => $data['target_type'] ?? null,
                'target_id' => $data['target_id'] ?? null,
                'icon' => $data['icon'] ?? null,
                'css_class' => $data['css_class'] ?? null,
                'open_new_tab' => (bool) ($data['open_new_tab'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($itemModel, $data['translations']);
        });

        $this->service->forgetMenu($menuModel->location);

        return $this->redirectWithSuccess(admin_route_name('menus.edit'), __('content::content.menu_item.updated'), ['menu' => $menuModel->id]);
    }

    public function destroy(int $menu, int $item): RedirectResponse
    {
        // Route-level `permission:content.delete` already gates this; no
        // additional check needed (was double-guarded; now consistent with
        // destroyAjax which relies on middleware alone).
        $menuModel = Menu::findOrFail($menu);
        $itemModel = MenuItem::where('menu_id', $menuModel->id)->findOrFail($item);

        // FK on `parent_id` uses nullOnDelete (per migration), so children survive as roots.
        // Acceptable: avoids surprise mass deletion. Admins can re-parent or delete them next.
        $itemModel->delete();

        $this->service->forgetMenu($menuModel->location);

        return $this->redirectWithSuccess(admin_route_name('menus.edit'), __('content::content.menu_item.deleted'), ['menu' => $menuModel->id]);
    }

    /**
     * AJAX partial update — accepts JSON with `sometimes` fields and per-locale translations.
     */
    public function updateAjax(UpdateMenuItemAjaxRequest $request, int $item): JsonResponse
    {
        $itemModel = MenuItem::with('translations', 'menu')->findOrFail($item);

        $this->updates->apply($itemModel, $request->validated());

        $itemModel = $itemModel->fresh(['translations']);
        $locale = app()->getLocale();
        $displayLabel = $itemModel->translations->firstWhere('locale', $locale)?->label
            ?? $itemModel->translations->first()?->label
            ?? '#'.$itemModel->id;

        return response()->json([
            'ok' => true,
            'item' => [
                'id' => $itemModel->id,
                'icon' => $itemModel->icon,
                'css_class' => $itemModel->css_class,
                'open_new_tab' => (bool) $itemModel->open_new_tab,
                'is_active' => (bool) $itemModel->is_active,
                'link_type' => $itemModel->link_type,
                'target_type' => $itemModel->target_type,
                'target_id' => $itemModel->target_id,
                'translations' => $itemModel->translations->map(fn ($t) => [
                    'locale' => $t->locale,
                    'label' => $t->label,
                    'url' => $t->url,
                ])->values(),
            ],
            'displayLabel' => $displayLabel,
        ]);
    }

    /**
     * AJAX delete — children are promoted to root via the existing nullOnDelete FK.
     */
    public function destroyAjax(int $item): JsonResponse
    {
        $itemModel = MenuItem::with('menu')->findOrFail($item);
        $menu = $itemModel->menu;

        $itemModel->delete();

        if ($menu) {
            $this->service->forgetMenu($menu->location);
        }

        return response()->json([
            'ok' => true,
            'id' => (int) $item,
        ]);
    }

    private function syncTranslations(MenuItem $item, array $translations): void
    {
        foreach ($translations as $row) {
            $item->translateOrNew($row['locale'])->fill([
                'label' => $row['label'],
                'url' => $row['url'] ?? null,
            ])->save();
        }
    }
}
