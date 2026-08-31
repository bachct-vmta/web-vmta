<?php

namespace Packages\Inquiry\Src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permission middleware on the admin route gates entry; reaffirming here keeps
        // the contract explicit if the route ever gets a wider middleware stack.
        return $this->user()?->hasPermission('inquiry.assign') ?? false;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
