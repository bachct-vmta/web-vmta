<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Packages\Content\Src\Http\Requests\Admin\StoreMenuRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdateMenuRequest;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Repositories\Interfaces\MenuRepositoryInterface;
use Packages\Content\Src\Services\MenuService;
use Packages\Core\Src\Http\Controllers\BaseController;

class MenuController extends BaseController
{
    public function __construct(
        private readonly MenuRepositoryInterface $repository,
        private readonly MenuService $service,
    ) {}

    public function index(): View
    {
        $menus = Menu::orderBy('location')->paginate(20);

        return view('content::admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        return view('content::admin.menus.form', [
            'menu' => new Menu(['is_active' => true]),
            'mode' => 'create',
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $menu = $this->repository->create([
            'name' => $data['name'],
            'location' => $data['location'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $this->service->forgetMenu($menu->location);

        return $this->redirectWithSuccess(admin_route_name('menus.edit'), __('content::content.menu.created'), ['menu' => $menu->id]);
    }

    public function edit(int $menu): View
    {
        $model = Menu::with('items.translations', 'items.children.translations')->findOrFail($menu);

        return view('content::admin.menus.form', [
            'menu' => $model,
            'mode' => 'edit',
            // Items grouped by depth: roots and their children separately so the form can render a tree.
            'rootItems' => $model->items->whereNull('parent_id')->sortBy('sort_order')->values(),
        ]);
    }

    public function update(UpdateMenuRequest $request, int $menu): RedirectResponse
    {
        $data = $request->validated();
        $model = Menu::findOrFail($menu);
        $oldLocation = $model->location;

        $model->update([
            'name' => $data['name'],
            'location' => $data['location'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        // Invalidate both the old and new location buckets so a rename clears stale cache.
        $this->service->forgetMenu($oldLocation);
        if ($oldLocation !== $model->location) {
            $this->service->forgetMenu($model->location);
        }

        return $this->redirectWithSuccess(admin_route_name('menus.edit'), __('content::content.menu.updated'), ['menu' => $model->id]);
    }

    public function destroy(int $menu): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('content.delete'), 403);

        $model = Menu::findOrFail($menu);
        $location = $model->location;

        // FK cascadeOnDelete on menu_items removes children automatically.
        $this->repository->delete($menu);

        // Repository bypasses MenuService cache hooks — bust manually so the public menu component
        // doesn't serve a deleted menu from cache.
        $this->service->forgetMenu($location);

        return $this->redirectWithSuccess(admin_route_name('menus.index'), __('content::content.menu.deleted'));
    }
}
