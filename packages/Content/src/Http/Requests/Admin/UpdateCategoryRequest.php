<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $id = (int) $this->route('category');

        // `different:N` compares against a FIELD named N, not a literal value — closure rejects
        // self-parenting (would create a 1-cycle in the categories tree).
        return [
            'parent_id' => [
                'nullable', 'integer', 'exists:categories,id',
                function ($attribute, $value, $fail) use ($id) {
                    if ((int) $value === $id) {
                        $fail(__('content::content.category.parent_self_invalid'));
                    }
                },
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::make($this, 'category_translations', 'category_id', $id ?: null),
            ],
            'translations.*.description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
