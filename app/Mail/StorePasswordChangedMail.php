<?php

namespace App\Mail;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StorePasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Store $store,
        public string $email,
        public string $newPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sua senha de acesso foi alterada',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.store-password-changed',
            with: [
                'store' => $this->store,
                'email' => $this->email,
                'newPassword' => $this->newPassword,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
