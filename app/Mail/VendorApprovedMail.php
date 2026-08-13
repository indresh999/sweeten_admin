<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly string $storeName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🎉 Your Store is Live on Sweetan!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.vendor.approved', with: [
            'fullName'  => $this->fullName,
            'storeName' => $this->storeName,
        ]);
    }
}
