<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Packages\Content\Src\Models\Menu;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            // Location is restricted to a fixed set so the public site can rely on
            // these slot keys being stable. Adding a new slot requires editing
            // Menu::LOCATIONS and the public layout that renders it.
            'location' => ['required', 'string', Rule::in(array_keys(Menu::LOCATIONS)), 'unique:menus,location'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
