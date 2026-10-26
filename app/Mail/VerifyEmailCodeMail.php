<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class VerifyEmailCodeMail extends Mailable
{
    public function __construct(
        public User $user,
        public string $code
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🔐 Código de verificación FusaShop: ' . $this->code);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-code',
            with: [
                'user' => $this->user,
                'code' => $this->code,
                'reportUrl' => URL::temporarySignedRoute(
                    'security.registration-report.show',
                    now()->addDays(30),
                    ['user' => $this->user->id]
                ),
            ]
        );
    }
}
