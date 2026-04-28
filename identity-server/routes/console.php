<?php

use App\Console\Commands\CleanupExpiredOtpsCommand;
use App\Console\Commands\CheckSystemHealthCommand;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Penjadwalan Task (Laravel 11 style — tanpa Kernel.php)
|--------------------------------------------------------------------------
*/

// Bersihkan OTP kedaluwarsa setiap jam
Schedule::command(CleanupExpiredOtpsCommand::class)
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::channel('security')
            ->error('Gagal menjalankan pembersihan OTP terjadwal.');
    });

// Pengecekan Kesehatan Sistem setiap menit
Schedule::command(CheckSystemHealthCommand::class)
    ->everyMinute()
    ->runInBackground()
    ->withoutOverlapping();


if (app()->environment(['local', 'testing'])) {
    Artisan::command('send-mail', function () {
        $apiKey = (string) config('services.mailtrap.api_key');
        $testEmail = env('MAIL_TEST_RECEIVER', 'hello@demomailtrap.co');

        if ($apiKey === '') {
            $this->error('MAILTRAP_API_KEY belum dikonfigurasi di .env');
            return self::FAILURE;
        }

        $email = (new \Mailtrap\Mime\MailtrapEmail())
            ->from(new \Symfony\Component\Mime\Address('hello@demomailtrap.co', 'Mailtrap Test'))
            ->to(new \Symfony\Component\Mime\Address($testEmail))
            ->subject('You are awesome!')
            ->category('Integration Test')
            ->text('Congrats for sending test email with Mailtrap!');

        try {
            $response = \Mailtrap\MailtrapClient::initSendingEmails(apiKey: $apiKey)->send($email);
            dump(\Mailtrap\Helper\ResponseHelper::toArray($response));
            $this->info("Email berhasil dikirim ke {$testEmail}");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Gagal mengirim email: " . $e->getMessage());
            return self::FAILURE;
        }
    })->purpose('Send Mail (local/testing only)');
}