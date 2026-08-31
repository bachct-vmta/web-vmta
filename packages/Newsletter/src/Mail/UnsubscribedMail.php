<?php

namespace Packages\Newsletter\Src\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;

class UnsubscribedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('newsletter::newsletter.unsubscribed_subject', [], $this->subscriber->locale),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'newsletter::emails.unsubscribed');
    }
}
