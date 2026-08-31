<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.create') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $menuId = (int) $this->route('menu');

        return [
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('menu_items', 'id')->where('menu_id', $menuId),
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
