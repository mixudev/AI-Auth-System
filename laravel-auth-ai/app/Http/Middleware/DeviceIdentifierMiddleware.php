<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DeviceIdentifierMiddleware
{
    /**
     * Pastikan setiap pengunjung memiliki ID perangkat (device_trust_id) yang stabil.
     * ID ini akan digunakan sebagai basis fingerprinting untuk Trusted Device.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah request sudah memiliki cookie ID perangkat
        $deviceId = $request->cookie('device_trust_id');
        $hasExistingId = !empty($deviceId);

        // 2. Jika tidak ada, buat UUID baru sebagai kandidat ID perangkat
        if (!$hasExistingId) {
            $deviceId = (string) Str::uuid();
            // Masukkan ke request agar bisa digunakan langsung oleh Controller/Service di request saat ini
            $request->cookies->add(['device_trust_id' => $deviceId]);
        }

        $response = $next($request);

        // 3. Jika baru dibuat, pasang cookie ke response agar tersimpan di browser
        if (!$hasExistingId && method_exists($response, 'withCookie')) {
            $response->withCookie(cookie(
                'device_trust_id',
                $deviceId,
                60 * 24 * 30, // 30 hari
                '/',
                null,
                $request->isSecure(), // Secure jika di HTTPS
                true, // HttpOnly agar tidak bisa dicuri via XSS
                false, // Raw (false karena di Larvel 11 kita kecualikan dari enkripsi di bootstrap/app.php)
                'Lax' // SameSite=Lax untuk kompatibilitas broad
            ));
        }

        return $response;
    }
}
