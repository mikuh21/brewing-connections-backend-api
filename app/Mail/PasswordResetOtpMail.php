<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
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
            subject: 'Your BrewHub Reset Code'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
