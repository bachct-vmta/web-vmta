<?php

namespace Packages\Inquiry\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    // Patient form shares the contact page with the partner form; scope its
    // validation errors to a named bag so they don't bleed into the other form.
    protected $errorBag = 'patient';

    protected function prepareForValidation(): void
    {
        $condition = trim((string) $this->input('condition', ''));
        $message = trim((string) $this->input('message', ''));

        if ($condition === '') {
            return;
        }

        $combinedMessage = $message === ''
            ? __('inquiry::inquiry.field_condition') . ': ' . $condition
            : __('inquiry::inquiry.field_condition') . ': ' . $condition . PHP_EOL . $message;

        $this->merge(['message' => $combinedMessage]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160'],
            // libphonenumber via propaganistas/laravel-phone: accepts VN domestic OR any valid international format.
            'phone' => ['required', 'string', 'max:30', 'phone:VN,INTERNATIONAL'],
            'condition' => ['nullable', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:5000'],
            'source_ref_type' => ['nullable', 'string', 'max:50'],
            'source_ref_id' => ['nullable', 'integer'],
            'consent_given' => ['sometimes', 'accepted'],
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
