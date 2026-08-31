<?php

namespace Packages\Catalog\Src\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Packages\Catalog\Src\Models\SpecialtyLead;

class SpecialtyLeadReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly SpecialtyLead $lead)
    {
    }

    public function envelope(): Envelope
    {
        $specialtyName = optional(
            $this->lead->specialty?->translate($this->lead->locale ?: 'vi')
                ?? $this->lead->specialty?->translations->first()
        )->name ?? '#'.$this->lead->specialty_id;

        return new Envelope(
            subject: '[VMTA] New lead — '.$specialtyName.' — '.$this->lead->name,
            replyTo: $this->lead->email ? [$this->lead->email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'catalog::mail.specialty-lead-received',
            with: ['lead' => $this->lead],
        );
    }
}
