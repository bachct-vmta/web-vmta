<?php

namespace Packages\Newsletter\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:160'],
            'consent_given' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_given.accepted' => __('newsletter::newsletter.consent_required'),
        ];
    }
}
