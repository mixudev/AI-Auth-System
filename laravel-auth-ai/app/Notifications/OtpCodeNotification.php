<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpCodeNotification extends Notification implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Notifikasi pengiriman kode OTP ke pengguna via email.
    |
    | Mengimplementasikan ShouldQueue agar pengiriman email tidak memblokir
    | respons HTTP (pengiriman dilakukan secara asynchronous).
    |--------------------------------------------------------------------------
    */

    use Queueable;

    public function __construct(
        private readonly string $otpCode
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresMinutes = config('security.otp.expires_minutes', 5);
        $appName        = config('app.name');

        return (new MailMessage)
            ->subject("[{$appName}] Kode Verifikasi Login Anda")
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Kami mendeteksi percobaan login ke akun Anda dari perangkat yang memerlukan verifikasi tambahan.')
            ->line('Gunakan kode berikut untuk menyelesaikan proses login:')
            ->line("**{$this->otpCode}**")
            ->line("Kode ini hanya berlaku selama **{$expiresMinutes} menit** dan hanya dapat digunakan satu kali.")
            ->line('Jika Anda tidak sedang melakukan login, segera abaikan email ini dan pertimbangkan untuk mengganti password Anda.')
            ->salutation('Salam, Tim Keamanan ' . $appName);
    }

    /**
     * Tentukan antrian yang digunakan untuk mengirim notifikasi ini.
     * Gunakan antrian berprioritas tinggi agar OTP terkirim cepat.
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications-high',
        ];
    }
}
