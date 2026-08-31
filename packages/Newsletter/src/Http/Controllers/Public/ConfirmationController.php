<?php

namespace Packages\Newsletter\Src\Http\Controllers\Public;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Newsletter\Src\Services\NewsletterService;

class ConfirmationController extends Controller
{
    public function __construct(private readonly NewsletterService $service) {}

    public function confirm(string $token): View
    {
        $subscriber = $this->service->confirm($token);

        return view('newsletter::public.confirm', [
            'subscriber' => $subscriber,
            'success' => $subscriber !== null,
        ]);
    }

    public function unsubscribe(Request $request): View
    {
        $email = (string) $request->query('email', '');

        if ($email === '') {
            return view('newsletter::public.unsubscribe', [
                'subscriber' => null,
                'success' => false,
            ]);
        }

        $subscriber = $this->service->unsubscribeByEmail($email);

        return view('newsletter::public.unsubscribe', [
            'subscriber' => $subscriber,
            'success' => $subscriber !== null,
        ]);
    }
}
