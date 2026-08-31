<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Packages\Content\Src\Models\Menu;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.edit') ?? false;
    }

    public function rules(): array
    {
        $id = (int) $this->route('menu');

        return [
            'name' => ['required', 'string', 'max:120'],
            'location' => [
                'required',
                'string',
                Rule::in(array_keys(Menu::LOCATIONS)),
                Rule::unique('menus', 'location')->ignore($id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
