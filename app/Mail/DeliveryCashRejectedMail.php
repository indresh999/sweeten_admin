<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryCashRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly float  $amount,
        public readonly string $reason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "❌ Payment Rejected — ₹{$this->amount}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.cash_rejected', with: [
            'fullName' => $this->fullName,
            'amount'   => $this->amount,
            'reason'   => $this->reason,
        ]);
    }
}
