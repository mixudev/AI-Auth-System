<?php

namespace App\Modules\AuditLog\Middleware;

use App\Modules\AuditLog\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AuditMiddleware
 * 
 * Middleware ini berfungsi untuk melacak dan mencatat aktivitas pengguna secara otomatis.
 * Biasanya diterapkan pada route-route penting seperti Create, Update, Delete.
 * Informasi yang dicatat meliputi data request, IP, dan User Agent.
 */
class AuditMiddleware
{
    /**
     * Jalankan middleware.
     * 
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string|null  $event  Nama event kustom (opsional)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $event = null)
    {
        // Jalankan request terlebih dahulu agar kita bisa tahu jika ada data baru/lama
        $response = $next($request);

        // Hanya catat log jika user sudah login dan method bukan GET (atau jika event ditentukan manual)
        if (Auth::check() && ($request->method() !== 'GET' || $event)) {
            
            // Tentukan nama event: Gunakan parameter $event jika ada, jika tidak gunakan Method + URL
            $eventName = $event ?? ($request->method() . ': ' . $request->path());

            // Siapkan data untuk disimpan
            // Kita mengecualikan field sensitif seperti password
            $payload = $request->except(['password', 'password_confirmation', '_token', '_method']);

            AuditLog::create([
                'user_id'    => Auth::id(),
                'event'      => $eventName,
                'url'        => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => $payload, // Data yang dikirimkan oleh user
            ]);
        }

        return $response;
    }
}
