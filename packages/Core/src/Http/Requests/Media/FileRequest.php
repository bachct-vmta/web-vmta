<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files' => 'required|array|max:'.config('file-manager.limit_upload_files', 10),
            'files.*' => 'file|max:'.(int) (media_max_file_size() / 1024),
            'folderId' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => trans('core-media::media.validation.file_required'),
            'folderId.required' => trans('core-media::media.validation.folder_id_required'),
            'folderId.integer' => trans('core-media::media.validation.folder_id_integer'),
        ];
    }
}
