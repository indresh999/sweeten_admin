<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly string $storeName,
        public readonly string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '❌ Store Application Update — Sweetan');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.vendor.rejected', with: [
            'fullName'  => $this->fullName,
            'storeName' => $this->storeName,
            'reason'    => $this->reason,
        ]);
    }
}
