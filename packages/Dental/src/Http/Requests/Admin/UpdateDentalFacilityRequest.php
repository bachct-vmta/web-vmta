<?php

namespace Packages\Dental\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Packages\Dental\Src\Enums\PublishStatus;

class UpdateDentalFacilityRequest extends FormRequest
{
    use NormalisesGalleryInput;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('dental.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $facilityId = (int) $this->route('facility');

        return [
            'dental_category_id' => ['required', 'integer', 'exists:dental_categories,id'],
            'status' => ['required', Rule::in(array_column(PublishStatus::cases(), 'value'))],
            // Form không hiện ô này; controller đặt thời điểm khi chuyển sang published
            'published_at' => ['nullable', 'date'],
            'is_operating' => ['nullable', 'boolean'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'certificates_media_ids' => ['nullable', 'array'],
            'certificates_media_ids.*' => ['integer', 'exists:media_files,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::make($this, 'dental_facility_translations', 'dental_facility_id', $facilityId),
            ],
            'translations.*.address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
