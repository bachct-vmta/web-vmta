<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $id = (int) $this->route('page');

        return [
            'status' => ['required', Rule::in(['draft', 'published'])],
            'template' => ['nullable', 'string', 'max:50'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'published_at' => ['nullable', 'date', 'required_if:status,published'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::make($this, 'page_translations', 'page_id', $id ?: null),
            ],
            'translations.*.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.*.body' => ['nullable', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.seo_og_image' => ['nullable', 'string', 'max:500'],
        ];
    }
}
