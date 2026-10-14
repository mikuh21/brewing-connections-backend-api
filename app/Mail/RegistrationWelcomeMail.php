<?php

namespace App\\Mail;

use App\\Models\\User;
use Illuminate\\Bus\\Queueable;
use Illuminate\\Mail\\Mailable;
use Illuminate\\Mail\\Mailables\\Content;
use Illuminate\\Mail\\Mailables\\Envelope;
use Illuminate\\Queue\\SerializesModels;

class RegistrationWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $establishmentName;
    public string $establishmentType;

    public function __construct(User $user, string $establishmentName, string $establishmentType)
    {
        $this->user = $user;
        $this->establishmentName = $establishmentName;
        $this->establishmentType = $establishmentType;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to BrewHub — Your Registration Was Successful'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-welcome'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
