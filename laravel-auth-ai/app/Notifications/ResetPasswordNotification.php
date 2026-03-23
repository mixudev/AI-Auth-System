<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $email
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $baseUrl = rtrim(config('app.url'), '/');
        
        $url = $baseUrl . route('password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ], false); // false agar menghasilkan path relatif, lalu kita gabung dengan baseUrl

        return (new MailMessage)
            ->subject("[{$appName}] Instruksi Reset Password")
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.')
            ->action('Reset Password', $url)
            ->line('Link reset password ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini dan pastikan akun Anda tetap aman.')
            ->salutation('Salam, Tim Keamanan ' . $appName);
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications-high',
        ];
    }
}
