<?php

namespace App\Modules\WaGateway\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\WaGateway\Models\WaGatewayTemplate;

class WaGatewayTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Security Alert (Critical)',
                'slug' => 'security-alert',
                'purpose' => 'security',
                'content' => "🚨 *SECURITY ALERT* 🚨\n\n🚩 *Event:* {event}\n📅 *Waktu:* {time}\n🌐 *IP Address:* {ip}\n👤 *User:* {user}\n\n🔍 Periksa dashboard untuk detail lebih lanjut.",
            ],
            [
                'name' => 'Authentication OTP',
                'slug' => 'auth-otp',
                'purpose' => 'auth',
                'content' => "🔐 *KODE VERIFIKASI*\n\nKode OTP Anda adalah: *{otp}*\n\nJangan bagikan kode ini kepada siapapun. Kode berlaku selama 5 menit.",
            ],
            [
                'name' => 'System Notification',
                'slug' => 'system-info',
                'purpose' => 'info',
                'content' => "🔔 *NOTIFIKASI SISTEM*\n\nHalo {user},\n\n{message}\n\nTerima kasih,\nTeam {app_name}",
            ],
            [
                'name' => 'Test Connection',
                'slug' => 'test-message',
                'purpose' => 'system',
                'content' => "✅ *WHATSAPP GATEWAY TEST*\n\nKoneksi berhasil terhubung!\nGateway: {gateway_name}\nWaktu: {time}",
            ],
        ];

        foreach ($templates as $tpl) {
            WaGatewayTemplate::updateOrCreate(
                ['slug' => $tpl['slug']],
                $tpl
            );
        }
    }
}
