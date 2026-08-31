<?php

namespace Packages\Core\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id ?? $this->route('role');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,'.$roleId,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'is_default' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên vai trò.',
            'slug.unique' => 'Slug này đã được sử dụng.',
        ];
    }
}
