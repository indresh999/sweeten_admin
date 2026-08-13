<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryDocumentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly string $docLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "✅ Document Approved — {$this->docLabel}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.document_approved', with: [
            'fullName' => $this->fullName,
            'docLabel' => $this->docLabel,
        ]);
    }
}
