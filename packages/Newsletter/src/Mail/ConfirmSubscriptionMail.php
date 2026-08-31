<?php

namespace Packages\Newsletter\Src\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;

class ConfirmSubscriptionMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public readonly string $confirmUrl;

    public function __construct(public NewsletterSubscriber $subscriber)
    {
        $this->confirmUrl = route('newsletter.confirm', ['token' => $subscriber->opt_in_token], absolute: true);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('newsletter::newsletter.confirm_subject', [], $this->subscriber->locale),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'newsletter::emails.confirm');
    }
}
