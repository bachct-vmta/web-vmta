<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Services\SettingService;

class SettingController extends BaseController
{
    public function __construct(private readonly SettingService $service) {}

    public function index(): View
    {
        $groups = $this->service->allGrouped();

        return view('core::admin.settings.index', [
            'groups' => $groups,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = $request->input('settings', []);
        if (! is_array($payload)) {
            $payload = [];
        }

        $this->service->updateMany($payload);

        return back()->with('status', __('core::settings.updated'));
    }

    public function testMail(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'nullable|email']);

        $recipient = $request->input('email')
            ?: $this->firstNotificationRecipient()
            ?: $request->user()?->email;

        if (! $recipient) {
            return back()->withErrors(['email' => __('core::settings.test_mail.no_recipient')]);
        }

        try {
            Mail::raw(__('core::settings.test_mail.body'), function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject(__('core::settings.test_mail.subject'));
            });

            return back()->with('status', __('core::settings.test_mail.sent', ['email' => $recipient]));
        } catch (\Throwable $e) {
            return back()->withErrors(['smtp' => __('core::settings.test_mail.failed').': '.$e->getMessage()]);
        }
    }

    protected function firstNotificationRecipient(): ?string
    {
        $list = (string) setting('mail.notification_recipients', '');
        foreach (explode(',', $list) as $email) {
            $email = trim($email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }
}
