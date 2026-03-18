<?php

use App\Console\Commands\CleanupExpiredOtpsCommand;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

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


Artisan::command('send-mail', function () {
    $email = (new MailtrapEmail())
        ->from(new Address('hello@demomailtrap.co', 'Mailtrap Test'))
        ->to(new Address('lazamart357@gmail.com'))
        ->subject('You are awesome!')
        ->category('Integration Test')
        ->text('Congrats for sending test email with Mailtrap!')
    ;

    $response = MailtrapClient::initSendingEmails(
        apiKey: '64a6a1f0455db0153c693916eb86fd66'
    )->send($email);

    var_dump(ResponseHelper::toArray($response));
})->purpose('Send Mail');