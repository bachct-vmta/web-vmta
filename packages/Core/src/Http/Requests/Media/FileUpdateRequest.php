<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class FileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
            'name' => 'nullable|string',
            'alt' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => trans('core-media::media.validation.file_required'),
            'id.numeric' => trans('core-media::media.validation.file_id_required'),
        ];
    }
}
