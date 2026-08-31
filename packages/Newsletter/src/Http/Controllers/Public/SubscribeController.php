<?php

namespace Packages\Newsletter\Src\Http\Controllers\Public;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Packages\Newsletter\Src\Http\Requests\SubscribeRequest;
use Packages\Newsletter\Src\Services\NewsletterService;

class SubscribeController extends Controller
{
    public function __construct(private readonly NewsletterService $service) {}

    public function store(SubscribeRequest $request): RedirectResponse
    {
        [, $alreadyConfirmed] = $this->service->subscribe(
            email: (string) $request->validated('email'),
            locale: app()->getLocale(),
            ip: $request->ip(),
        );

        $messageKey = $alreadyConfirmed
            ? 'newsletter::newsletter.already_subscribed'
            : 'newsletter::newsletter.confirm_email_sent';

        return back()->with('status', __($messageKey));
    }
}
