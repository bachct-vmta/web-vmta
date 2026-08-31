<?php

namespace Packages\Catalog\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    use NormalisesGalleryInput;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('catalog.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $id = (int) $this->route('service');

        return [
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', 'exists:media_files,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'regex:/^[A-Za-z]{3,5}$/'],
            'cta_app_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date', 'required_if:status,published'],

            'specialty_ids' => ['nullable', 'array'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::make($this, 'service_translations', 'service_id', $id ?: null),
            ],
            'translations.*.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.*.body' => ['nullable', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.seo_og_image' => ['nullable', 'url', 'max:500'],
        ];
    }
}
