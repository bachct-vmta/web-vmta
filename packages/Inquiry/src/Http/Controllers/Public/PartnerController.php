<?php

namespace Packages\Inquiry\Src\Http\Controllers\Public;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Http\Requests\PartnerRequest;
use Packages\Inquiry\Src\Services\InquiryService;

class PartnerController extends Controller
{
    public function __construct(private readonly InquiryService $service) {}

    public function store(PartnerRequest $request): RedirectResponse
    {
        $payload = $request->validated() + [
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ];

        $inquiry = $this->service->capture(InquirySource::PartnerInquiry, $payload, InquiryPriority::Normal);
        $this->service->dispatchEmails($inquiry);

        return back()->with('partner_status', __('inquiry::inquiry.thank_you'));
    }
}
