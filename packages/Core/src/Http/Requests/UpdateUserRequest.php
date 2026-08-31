<?php

namespace Packages\Core\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$userId,
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'phone' => 'nullable|string|max:20',
            'role_id' => 'nullable|exists:roles,id',
            'balance' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
