<?php

namespace Packages\Inquiry\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Packages\Inquiry\Src\Enums\InquiryStatus;

class TransitionInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('inquiry.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_map(fn ($c) => $c->value, InquiryStatus::cases()))],
            'note' => ['nullable', 'string', 'min:1', 'max:2000'],
        ];
    }
}
