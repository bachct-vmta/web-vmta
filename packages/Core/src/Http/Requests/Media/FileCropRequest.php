<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class FileCropRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_id' => 'required|integer',
            'crop_data' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'image_id.required' => trans('core-media::media.validation.image_required'),
            'image_id.integer' => trans('core-media::media.validation.image_id_integer'),
            'crop_data.required' => trans('core-media::media.validation.crop_data_required'),
        ];
    }
}
