<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $menuId = (int) $this->route('menu');
        $itemId = (int) $this->route('item');

        return [
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('menu_items', 'id')->where('menu_id', $menuId),
                // Reject self-parenting (immediate 1-cycle). Deeper cycles deferred to UX.
                function ($attribute, $value, $fail) use ($itemId) {
                    if ((int) $value === $itemId) {
                        $fail(__('content::content.menu.parent_self_invalid'));
                    }
                },
            ],
            'link_type' => ['required', Rule::in(['url', 'morph'])],
            'target_type' => ['nullable', 'string', 'max:50', 'required_if:link_type,morph'],
            'target_id' => ['nullable', 'integer', 'min:1', 'required_if:link_type,morph'],
            'icon' => ['nullable', 'string', 'max:60'],
            'css_class' => ['nullable', 'string', 'max:255'],
            'open_new_tab' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.label' => ['required', 'string', 'max:255'],
            'translations.*.url' => ['nullable', 'string', 'max:500', 'required_if:link_type,url'],
        ];
    }
}
