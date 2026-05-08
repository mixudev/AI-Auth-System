<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * SsoSecurityHeadersMiddleware
 * ----------------------------
 * Menambahkan HTTP Security Headers ke semua response SSO.
 * Melindungi dari: Clickjacking, MIME sniffing, XSS reflection, CSRF via Referrer,
 * dan protocol downgrade (HSTS).
 *
 * [FIX] 'unsafe-inline' dihapus dari script-src dan style-src.
 * Diganti dengan nonce-based CSP yang konsisten dengan SecurityHeadersMiddleware.
 *
 * Daftarkan di SSOServiceProvider pada route group SSO.
 */
class SsoSecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Cegah konten dimuat dalam iframe (Clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Cegah browser menebak content-type (MIME Sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Protection legacy browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Batasi referrer yang dikirim ke request lintas-origin
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Paksa HTTPS selama 1 tahun (hanya di production)
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Ambil nonce yang sudah di-share oleh SecurityHeadersMiddleware (jika ada),
        // atau generate nonce baru khusus untuk SSO pages.
        $nonce = view()->shared('cspNonce') ?? Str::random(40);

        // [FIX] Hapus 'unsafe-inline' dari script-src dan style-src.
        // Semua inline script/style di Blade views harus menggunakan atribut
        // nonce="{{ $cspNonce }}" agar diizinkan CSP.
        $csp = implode('; ', [
            "default-src 'self'",
            // [FIX] Ganti 'unsafe-inline' dengan nonce — cegah arbitrary JS injection
            "script-src 'self' 'nonce-{$nonce}'",
            // [FIX] Ganti 'unsafe-inline' dengan nonce — cegah style injection XSS
            "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "upgrade-insecure-requests",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Menonaktifkan fitur browser yang tidak diperlukan
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        return $response;
    }
}
