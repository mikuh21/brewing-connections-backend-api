<?php

namespace App\Mail;

use App\Models\Establishment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstablishmentLocationNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Establishment $establishment;
    public string $ownerName;
    public int $incorrectCount;

    public function __construct(Establishment $establishment, string $ownerName, int $incorrectCount)
    {
        $this->establishment = $establishment;
        $this->ownerName = $ownerName;
        $this->incorrectCount = $incorrectCount;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Requested: Verify Your BrewHub Establishment Location'
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.establishment-location-notice');
    }

    public function attachments(): array
    {
        return [];
    }
}
