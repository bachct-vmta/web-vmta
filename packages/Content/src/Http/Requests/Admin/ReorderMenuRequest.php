<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Packages\Content\Src\Models\MenuItem;

class ReorderMenuRequest extends FormRequest
{
    public const MAX_DEPTH = 3;

    public const MAX_ITEMS = 200;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'tree' => ['required', 'array', 'min:1'],
            'tree.*.id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $tree = $this->input('tree', []);
            $ids = [];
            $depthOk = $this->walkTree($tree, 1, $ids);

            if (! $depthOk) {
                $v->errors()->add('tree', __('content::content.menu.reorder.depth_exceeded'));

                return;
            }

            if (count($ids) > self::MAX_ITEMS) {
                $v->errors()->add('tree', __('content::content.menu.reorder.too_many_items'));

                return;
            }

            if (count($ids) === 0) {
                $v->errors()->add('tree', __('content::content.menu.reorder.error'));

                return;
            }

            $menuId = (int) $this->route('menu');
            $found = MenuItem::query()
                ->whereIn('id', $ids)
                ->where('menu_id', $menuId)
                ->count();

            if ($found !== count(array_unique($ids))) {
                $v->errors()->add('tree', __('content::content.menu.reorder.cross_menu'));
            }
        });
    }

    private function walkTree(array $nodes, int $depth, array &$ids): bool
    {
        if ($depth > self::MAX_DEPTH) {
            return false;
        }

        foreach ($nodes as $node) {
            if (! isset($node['id'])) {
                continue;
            }
            $ids[] = (int) $node['id'];

            if (! empty($node['children']) && is_array($node['children'])) {
                if (! $this->walkTree($node['children'], $depth + 1, $ids)) {
                    return false;
                }
            }
        }

        return true;
    }
}
