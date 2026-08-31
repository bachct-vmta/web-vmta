<?php

namespace Packages\Catalog\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('catalog.edit') ?? false;
    }

    public function rules(): array
    {
        $id = (int) $this->route('specialty');

        return SpecialtyRequestRules::rules($this, $id ?: null);
    }

    public function prepareForValidation(): void
    {
        SpecialtyRequestRules::normalisePayload($this);
    }
}
