<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class CKEditorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'upload' => 'required|mimetypes:'.implode(',', config('file-manager.mime_types', [])),
        ];
    }

    public function messages(): array
    {
        return [
            'upload.required' => trans('core-media::media.validation.file_required'),
            'upload.mimetypes' => trans('core-media::media.validation.file_mime_types', [
                'values' => implode(', ', config('file-manager.mime_types', [])),
            ]),
        ];
    }
}
