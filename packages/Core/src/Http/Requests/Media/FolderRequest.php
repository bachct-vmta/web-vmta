<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class FolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable',
            'name' => 'required|string',
            'parent_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => trans('core-media::media.validation.folder_name_required'),
        ];
    }
}
