<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryOrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly int    $orderId,
        public readonly float  $earning
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "🎉 Order #$this->orderId Delivered — ₹{$this->earning} Earned!");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.order_delivered', with: [
            'fullName' => $this->fullName,
            'orderId'  => $this->orderId,
            'earning'  => $this->earning,
        ]);
    }
}
