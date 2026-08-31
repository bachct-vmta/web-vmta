<?php

namespace Packages\Inquiry\Src\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Packages\Inquiry\Src\Models\Inquiry;

class InquiryAutoReplyMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public readonly string $renderedSubject;

    public function __construct(public Inquiry $inquiry)
    {
        $this->renderedSubject = __('inquiry::inquiry.auto_reply_subject', [], $inquiry->locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->renderedSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'inquiry::emails.auto-reply',
        );
    }
}
