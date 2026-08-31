<?php

namespace Packages\Catalog\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('catalog.create') ?? false;
    }

    public function rules(): array
    {
        return SpecialtyRequestRules::rules($this, null);
    }

    public function prepareForValidation(): void
    {
        SpecialtyRequestRules::normalisePayload($this);
    }
}
