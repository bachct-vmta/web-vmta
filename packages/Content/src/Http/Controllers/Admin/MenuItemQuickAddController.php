<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Packages\Content\Src\Http\Requests\Admin\QuickAddMenuItemRequest;
use Packages\Content\Src\Services\MenuItemCreateService;
use Packages\Core\Src\Http\Controllers\BaseController;

class MenuItemQuickAddController extends BaseController
{
    public function __construct(private readonly MenuItemCreateService $service) {}

    public function __invoke(QuickAddMenuItemRequest $request, int $menu): JsonResponse
    {
        $item = $this->service->quickAdd($menu, $request->validated());

        $locale = app()->getLocale();
        $displayLabel = $item->translations->firstWhere('locale', $locale)?->label
            ?? $item->translations->first()?->label
            ?? '#'.$item->id;

        return response()->json([
            'ok' => true,
            'item' => [
                'id' => $item->id,
                'icon' => $item->icon,
                'link_type' => $item->link_type,
                'target_type' => $item->target_type,
                'target_id' => $item->target_id,
                'open_new_tab' => (bool) $item->open_new_tab,
                'is_active' => (bool) $item->is_active,
                'displayLabel' => $displayLabel,
                'translations' => $item->translations->map(fn ($t) => [
                    'locale' => $t->locale,
                    'label' => $t->label,
                    'url' => $t->url,
                ])->values(),
            ],
        ], 201);
    }
}
