<?php

namespace App\Mail;

use App\Models\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $purpose = Otp::PURPOSE_REGISTRATION,
        public string $recipientName = '',
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->purpose === Otp::PURPOSE_PASSWORD_RESET
                ? 'Reset your ' . settings('site_name', 'MarketLink') . ' password'
                : 'Your ' . settings('site_name', 'MarketLink') . ' verification code',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp-code');
    }
}
