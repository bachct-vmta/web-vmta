<?php

namespace Packages\Dental\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Packages\Dental\Src\Enums\PublishStatus;

class StoreDentalCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('dental.create') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));

        return [
            'status' => ['required', Rule::in(array_column(PublishStatus::cases(), 'value'))],
            // Form không hiện ô này; controller đặt thời điểm khi chuyển sang published
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::make($this, 'dental_category_translations', 'dental_category_id', null),
            ],
        ];
    }
}
