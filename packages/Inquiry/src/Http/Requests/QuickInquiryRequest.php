<?php

namespace Packages\Inquiry\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickInquiryRequest extends FormRequest
{
    /**
     * Morph aliases accepted on quick-inquiry. Hardcoded so this validation survives any
     * provider-load ordering shift; the owning packages register the same aliases at boot,
     * and persistence cannot bind to an unknown alias anyway.
     */
    private const ALLOWED_REF_TYPES = ['service', 'tour_package', 'combo', 'dental_service'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:160'],
            'phone' => ['required', 'string', 'max:30', 'phone:VN,INTERNATIONAL'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source_ref_type' => ['required', 'string', 'max:50', Rule::in(self::ALLOWED_REF_TYPES)],
            'source_ref_id' => ['required', 'integer', 'min:1'],
            'consent_given' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_given.accepted' => __('inquiry::inquiry.consent_required'),
            'phone.phone' => __('inquiry::inquiry.phone_invalid'),
            'source_ref_type.required' => __('inquiry::inquiry.source_ref_required'),
            'source_ref_type.in' => __('inquiry::inquiry.source_ref_invalid'),
            'source_ref_id.required' => __('inquiry::inquiry.source_ref_required'),
        ];
    }
}
