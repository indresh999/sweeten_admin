<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryCashSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly float  $amount,
        public readonly string $date
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "💰 Payment Submission — ₹{$this->amount}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.cash_submitted', with: [
            'fullName' => $this->fullName,
            'amount'   => $this->amount,
            'date'     => $this->date,
        ]);
    }
}
