<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryPayoutProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly float  $amount,
        public readonly string $method
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "💰 Payout of ₹{$this->amount} Processed!");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.payout_processed', with: [
            'fullName' => $this->fullName,
            'amount'   => $this->amount,
            'method'   => $this->method,
        ]);
    }
}
