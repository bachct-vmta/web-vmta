<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'view_in' => 'nullable|string',
            'sort_by' => 'nullable|string',
            'folder_id' => 'nullable|string',
            'search' => 'nullable|string',
            'load_more' => 'required',
            'paged' => 'required|integer',
            'posts_per_page' => 'required|integer',
            'ids' => 'nullable',
            'type' => 'nullable',
            'filter_type' => 'nullable|string|in:image,video,document,everything',
        ];
    }

    public function messages(): array
    {
        return [
            'load_more.required' => trans('core-media::media.validation.load_more_required'),
        ];
    }
}
