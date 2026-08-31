<?php

namespace Packages\Inquiry\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Http\Requests\ContactRequest;
use Packages\Inquiry\Src\Services\ContactPageService;
use Packages\Inquiry\Src\Services\InquiryService;

class ContactController extends Controller
{
    public function __construct(
        private readonly InquiryService $service,
        private readonly ContactPageService $contactPage,
    ) {}

    public function show(): View
    {
        SEOTools::setTitle((string) __('inquiry::inquiry.page_title'));
        SEOTools::opengraph()->setUrl(url()->current());

        $sections = $this->contactPage->getRenderData()['sections']
            ->keyBy(fn ($s) => $s->position->value);

        return view('inquiry::public.contact', [
            'heroSection'    => $sections->get('hero'),
            'officesSection' => $sections->get('offices'),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $payload = $request->validated() + [
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ];

        $inquiry = $this->service->capture(InquirySource::ContactForm, $payload, InquiryPriority::Normal);
        $this->service->dispatchEmails($inquiry);

        return back()->with('status', __('inquiry::inquiry.thank_you'));
    }
}
