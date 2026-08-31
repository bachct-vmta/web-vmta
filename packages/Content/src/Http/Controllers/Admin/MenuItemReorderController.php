<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Packages\Content\Src\Http\Requests\Admin\ReorderMenuRequest;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Services\MenuItemReorderService;
use Packages\Core\Src\Http\Controllers\BaseController;

class MenuItemReorderController extends BaseController
{
    public function __construct(private readonly MenuItemReorderService $service) {}

    public function __invoke(ReorderMenuRequest $request, int $menu): JsonResponse
    {
        Menu::query()->whereKey($menu)->firstOrFail();

        // Use raw input rather than validated() — Laravel's rule-driven array
        // narrowing would strip the nested `children` keys we need to walk.
        // Validation (depth, item count, cross-menu ownership) already ran via
        // ReorderMenuRequest::withValidator() on the same raw payload.
        $this->service->apply($menu, (array) $request->input('tree', []));

        return response()->json([
            'ok' => true,
            'savedAt' => now()->toIso8601String(),
        ]);
    }
}
