<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryDocumentsSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly string $email
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📄 Documents Received — Under Review');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.documents_submitted', with: [
            'fullName' => $this->fullName,
        ]);
    }
}
