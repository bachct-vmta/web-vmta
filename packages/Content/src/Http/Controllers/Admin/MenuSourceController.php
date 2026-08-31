<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Packages\Content\Src\Services\MenuSourceService;
use Packages\Core\Src\Http\Controllers\BaseController;

class MenuSourceController extends BaseController
{
    public function __construct(private readonly MenuSourceService $service) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(MenuSourceService::ALLOWED_TYPES)],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = $this->service->search(
            type: $data['type'],
            query: (string) ($data['q'] ?? ''),
            page: (int) ($data['page'] ?? 1),
        );

        return response()->json($result);
    }
}
