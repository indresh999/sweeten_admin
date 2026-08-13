<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryDocumentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly string $docLabel,
        public readonly string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "❌ Document Rejected — {$this->docLabel}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.document_rejected', with: [
            'fullName' => $this->fullName,
            'docLabel' => $this->docLabel,
            'reason'   => $this->reason,
        ]);
    }
}
