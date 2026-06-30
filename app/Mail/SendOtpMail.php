<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int    $otp,
        public readonly string $email
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Sweetan OTP — ' . $this->otp);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp', with: ['otp' => $this->otp, 'email' => $this->email]);
    }
}
