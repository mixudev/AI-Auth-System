<?php

namespace App\Modules\Security\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Resolusi kode negara berdasarkan alamat IP.
     * Menggunakan ip-api.com (Free Tier) dengan caching 24 jam.
     */
    public function getCountryCode(string $ip): string
    {
        // Jangan lakukan lookup untuk IP lokal/private
        if ($this->isPrivateIp($ip)) {
            return 'ZZ'; // Unknown/Private network
        }

        return Cache::remember("geoip:v2:{$ip}", now()->addHours(24), function () use ($ip) {
            try {
                // Gunakan provider HTTPS untuk menghindari manipulasi traffic.
                $response = Http::timeout(3)
                    ->get("https://ipwho.is/{$ip}");

                if ($response->successful() && $response->json('success') === true) {
                    return (string) $response->json('country_code');
                }

                Log::channel('security')->warning('GeoIP Lookup gagal atau IP tidak ditemukan', [
                    'ip'      => $ip,
                    'status'  => $response->status(),
                    'message' => $response->json('message')
                ]);

            } catch (\Exception $e) {
                Log::channel('security')->error('Kesalahan koneksi GeoIP API', [
                    'ip'    => $ip,
                    'error' => $e->getMessage()
                ]);
            }

            return 'ZZ'; // Unknown country
        });
    }

    /**
     * Periksa apakah IP merupakan alamat privat/lokal.
     */
    private function isPrivateIp(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
