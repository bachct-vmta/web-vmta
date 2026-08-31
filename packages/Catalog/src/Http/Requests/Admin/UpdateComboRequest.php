<?php

namespace Packages\Catalog\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComboRequest extends FormRequest
{
    use NormalisesGalleryInput;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('catalog.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $id = (int) $this->route('combo');

        return [
            'cover_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media_files,id'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'between:0,100'],
            'currency' => ['nullable', 'string', 'regex:/^[A-Za-z]{3,5}$/'],
            'cta_app_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date', 'required_if:status,published'],

            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'tour_package_ids' => ['nullable', 'array'],
            'tour_package_ids.*' => ['integer', 'exists:tour_packages,id'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::make($this, 'combo_translations', 'combo_id', $id ?: null),
            ],
            'translations.*.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.*.body' => ['nullable', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.seo_og_image' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $services = $this->input('service_ids', []);
            $tours = $this->input('tour_package_ids', []);
            if (empty($services) && empty($tours)) {
                $v->errors()->add('service_ids', __('catalog::catalog.errors.combo_empty'));
            }
        });
    }
}
