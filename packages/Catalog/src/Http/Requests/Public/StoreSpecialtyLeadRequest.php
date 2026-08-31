<?php

namespace Packages\Catalog\Src\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:64', 'regex:/^[0-9+\-\s().]{6,}$/'],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'demand' => ['nullable', 'string', 'max:500'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'hp_field' => ['nullable', 'string', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => __('catalog::public.specialties.lead.consent_required'),
            'phone.regex' => __('catalog::public.specialties.lead.phone_invalid'),
        ];
    }
}
