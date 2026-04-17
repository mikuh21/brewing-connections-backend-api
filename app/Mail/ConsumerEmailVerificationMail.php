<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsumerEmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $otp;
    public int $expiryMinutes;

    public function __construct(User $user, string $otp, int $expiryMinutes = 15)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->expiryMinutes = $expiryMinutes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify your BrewHub email'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consumer-email-verification'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
