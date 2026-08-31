<?php

namespace Packages\Inquiry\Src\Http\Controllers\Public;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Http\Requests\EmergencyRequest;
use Packages\Inquiry\Src\Services\InquiryService;

class EmergencyController extends Controller
{
    public function __construct(private readonly InquiryService $service) {}

    public function show(): View
    {
        return view('inquiry::public.emergency');
    }

    public function store(EmergencyRequest $request): RedirectResponse
    {
        $payload = $request->validated() + [
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ];

        $inquiry = $this->service->capture(InquirySource::Emergency, $payload, InquiryPriority::Urgent);
        $this->service->dispatchEmails($inquiry);

        return back()->with('status', __('inquiry::inquiry.thank_you'));
    }
}
