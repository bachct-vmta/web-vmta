<?php

namespace Packages\Inquiry\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmergencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'phone:VN,INTERNATIONAL'],
            'message' => ['nullable', 'string', 'max:1000'],
            'consent_given' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_given.accepted' => __('inquiry::inquiry.consent_required'),
            'phone.phone' => __('inquiry::inquiry.phone_invalid'),
        ];
    }
}
