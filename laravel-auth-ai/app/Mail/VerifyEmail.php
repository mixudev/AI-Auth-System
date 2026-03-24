<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * VerifyEmail Mailable
 *
 * Contoh penggunaan:
 *   Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
 *
 * Atau via queue:
 *   Mail::to($user->email)->queue(new VerifyEmail($user, $verificationUrl));
 */
class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly string $actionUrl,
        public readonly string $expiresIn  = '24 jam',
        public readonly string $unsubscribeUrl = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                config('mail.from.name'),
            ),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address('support@' . parse_url(config('app.url'), PHP_URL_HOST)),
            ],
            subject: 'Verifikasi Alamat Email Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.examples.verify',        // HTML
            text: 'emails.examples.verify-text',   // Plain-text (fix MIME_HTML_ONLY)
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Mailer'              => config('app.name') . ' Mailer',
                'X-Priority'            => '3',
                'List-Unsubscribe'      => '<' . ($this->unsubscribeUrl ?: config('app.url') . '/unsubscribe') . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
