<?php

namespace Packages\Inquiry\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
{
    // Partner form shares the contact page with the patient form; scope its
    // validation errors to a named bag so they don't bleed into the other form.
    protected $errorBag = 'partner';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:120'],
            'email'        => ['required', 'email:rfc', 'max:160'],
            'phone'        => ['required', 'string', 'max:30', 'phone:VN,INTERNATIONAL'],
            'industry'     => ['nullable', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:120'],
            'message'      => ['nullable', 'string', 'max:5000'],
            'consent_given' => ['sometimes', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_given.accepted' => __('inquiry::inquiry.consent_required'),
            'phone.phone'            => __('inquiry::inquiry.phone_invalid'),
        ];
    }
}
