<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryProfileActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🎉 Congratulations! Your Sweetan Account is Active!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.profile_activated', with: [
            'fullName' => $this->fullName,
        ]);
    }
}
