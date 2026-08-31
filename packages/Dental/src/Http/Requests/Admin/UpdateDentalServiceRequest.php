<?php

namespace Packages\Dental\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Packages\Dental\Src\Enums\PublishStatus;

class UpdateDentalServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('dental.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $serviceId = (int) $this->route('service');
        $facilityId = $this->input('dental_facility_id') !== null
            ? (int) $this->input('dental_facility_id')
            : null;

        return [
            'dental_facility_id' => ['required', 'integer', 'exists:dental_facilities,id'],
            'status' => ['required', Rule::in(array_column(PublishStatus::cases(), 'value'))],
            // Form dịch vụ không hiện ô này; controller đặt thời điểm khi chuyển sang published
            'published_at' => ['nullable', 'date'],
            'icon_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'video_poster_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::forService($this, $facilityId, $serviceId),
            ],
            'translations.*.hero_h1' => ['nullable', 'string', 'max:255'],
            'translations.*.video_caption' => ['nullable', 'string', 'max:255'],
            'translations.*.body' => ['nullable', 'string'],
            'translations.*.comparison_html' => ['nullable', 'string'],
            'translations.*.price_table_html' => ['nullable', 'string'],
        ];
    }
}
