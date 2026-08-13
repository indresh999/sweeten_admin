<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryOrderAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fullName,
        public readonly int    $orderId,
        public readonly string $shopName,
        public readonly string $deliveryAddress,
        public readonly float  $orderAmount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "📦 New Order #$this->orderId Assigned to You");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.delivery.order_assigned', with: [
            'fullName'         => $this->fullName,
            'orderId'          => $this->orderId,
            'shopName'         => $this->shopName,
            'deliveryAddress'  => $this->deliveryAddress,
            'orderAmount'      => $this->orderAmount,
        ]);
    }
}
