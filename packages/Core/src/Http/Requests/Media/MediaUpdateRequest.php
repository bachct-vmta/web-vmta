<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required',
            'name' => 'nullable|string',
            'is_folder' => 'required',
        ];
    }
}
