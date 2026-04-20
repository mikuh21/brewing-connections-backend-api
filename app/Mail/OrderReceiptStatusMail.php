<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceiptStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public array $receiptMeta,
        public string $reservationCode,
        public string $receiptUrl,
        public string $eventType = 'created',
    ) {
    }

    public function envelope(): Envelope
    {
        $status = ucfirst((string) ($this->order->status ?? 'pending'));

        $subject = $this->eventType === 'status_updated'
            ? "BrewHub Order Update: {$this->reservationCode} is now {$status}"
            : "BrewHub Reservation Confirmed: {$this->reservationCode}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-receipt-status',
            with: [
                'order' => $this->order,
                'receiptMeta' => $this->receiptMeta,
                'reservationCode' => $this->reservationCode,
                'receiptUrl' => $this->receiptUrl,
                'eventType' => $this->eventType,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
