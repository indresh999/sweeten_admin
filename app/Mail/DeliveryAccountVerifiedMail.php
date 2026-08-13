<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryAccountVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly string $email
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🎉 Your Delivery Account is Verified!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.account_verified', with: [
            'fullName' => $this->fullName,
        ]);
    }
}
