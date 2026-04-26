<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Modules\Settings\Models\Setting;
use App\Modules\AuditLog\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * ConfigurationController
 * 
 * Pusat kendali konfigurasi sistem global yang menggabungkan
 * Pengaturan Sistem, Kebijakan Keamanan, dan Konfigurasi Layanan.
 */
class ConfigurationController
{
    /**
     * Tampilkan halaman konfigurasi terpadu.
     */
    public function index(): View
    {
        $settings = [
            // General Settings
            'site_name'            => Setting::get('site_name', config('app.name')),
            'site_description'     => Setting::get('site_description', ''),
            
            // SSO & Token Policy
            'token_expiry_access'  => Setting::get('token_expiry_access', 60),
            'token_expiry_refresh' => Setting::get('token_expiry_refresh', 1440),

            // Security Policy
            'password_min_length'      => Setting::get('password_min_length', 8),
            'password_require_symbols' => Setting::get('password_require_symbols', false),
            'password_require_numbers' => Setting::get('password_require_numbers', false),
            'max_login_attempts'       => Setting::get('max_login_attempts', 5),
            'lockout_duration'         => Setting::get('lockout_duration', 15),
            'force_mfa_admin'          => Setting::get('force_mfa_admin', false),
            'ip_whitelist'             => Setting::get('ip_whitelist', ''),

            // Mail Configuration
            'mail_host'            => Setting::get('mail_host', config('mail.mailers.smtp.host')),
            'mail_port'            => Setting::get('mail_port', config('mail.mailers.smtp.port')),
            'mail_username'        => Setting::get('mail_username', config('mail.mailers.smtp.username')),
            'mail_password'        => Setting::get('mail_password', config('mail.mailers.smtp.password')),
            'mail_encryption'      => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'mail_from_address'    => Setting::get('mail_from_address', config('mail.from.address')),
            'mail_from_name'       => Setting::get('mail_from_name', config('mail.from.name')),
        ];

        return view('settings::Admin.configurations.index', compact('settings'));
    }

    /**
     * Simpan perubahan konfigurasi.
     */
    public function update(Request $request): RedirectResponse
    {
        $group = $request->input('group', 'general');

        // [SECURITY] Verifikasi Password Admin sebelum menyimpan
        if (! \Illuminate\Support\Facades\Hash::check($request->input('admin_password'), Auth::user()->password)) {
            return back()->with('error', 'Verifikasi password gagal. Konfigurasi tidak disimpan demi alasan keamanan.');
        }

        // Validasi berdasarkan grup
        $rules = [];
        if ($group === 'general') {
            $rules = [
                'site_name'        => 'required|string|max:100',
                'site_description' => 'nullable|string|max:255',
            ];
        } elseif ($group === 'security') {
            $rules = [
                'password_min_length'      => 'required|integer|min:6',
                'password_require_symbols' => 'boolean',
                'password_require_numbers' => 'boolean',
                'max_login_attempts'       => 'required|integer|min:1',
                'lockout_duration'         => 'required|integer|min:1',
                'force_mfa_admin'          => 'boolean',
                'ip_whitelist'             => 'nullable|string',
            ];
        } elseif ($group === 'sso') {
            $rules = [
                'token_expiry_access'  => 'required|integer|min:1',
                'token_expiry_refresh' => 'required|integer|min:1',
            ];
        } elseif ($group === 'mail') {
            $rules = [
                'mail_host'         => 'nullable|string',
                'mail_port'         => 'nullable|integer',
                'mail_username'     => 'nullable|string',
                'mail_password'     => 'nullable|string',
                'mail_encryption'   => 'nullable|string',
                'mail_from_address' => 'nullable|email',
                'mail_from_name'    => 'nullable|string',
            ];
        }

        $validated = $request->validate($rules);

        // Khusus checkbox di security
        if ($group === 'security') {
            $validated['password_require_symbols'] = $request->has('password_require_symbols');
            $validated['password_require_numbers'] = $request->has('password_require_numbers');
            $validated['force_mfa_admin'] = $request->has('force_mfa_admin');
        }

        $oldValues = [];
        foreach ($validated as $key => $value) {
            $oldValues[$key] = Setting::get($key);
            
            $type = 'string';
            if ($key === 'mail_password') {
                $type = 'encrypted';
            } elseif (is_bool($value) || in_array($key, ['password_require_symbols', 'password_require_numbers', 'force_mfa_admin'])) {
                $type = 'boolean';
            } elseif (is_numeric($value)) {
                $type = 'integer';
            }

            Setting::set($key, $value, $group, $type);
        }

        // Konversi hari ke menit khusus untuk refresh token jika ada di request
        if ($group === 'sso' && isset($validated['token_expiry_refresh'])) {
            Setting::set('token_expiry_refresh', $validated['token_expiry_refresh'] * 1440, 'sso', 'integer');
        }

        // Audit Log
        AuditLog::create([
            'user_id'    => Auth::id(),
            'event'      => 'update_config_' . $group,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'url'        => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Konfigurasi ' . ucfirst($group) . ' berhasil diperbarui.');
    }

    /**
     * Uji coba koneksi SMTP dengan parameter yang diberikan (tanpa menyimpan).
     */
    public function testMail(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'mail_host'         => 'required|string',
            'mail_port'         => 'required|integer',
            'mail_username'     => 'nullable|string',
            'mail_password'     => 'nullable|string',
            'mail_encryption'   => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name'    => 'required|string',
        ]);

        try {
            // Override Mail Config secara runtime untuk sesi ini saja
            config([
                'mail.mailers.smtp.host'       => $validated['mail_host'],
                'mail.mailers.smtp.port'       => $validated['mail_port'],
                'mail.mailers.smtp.username'   => $validated['mail_username'],
                'mail.mailers.smtp.password'   => $validated['mail_password'],
                'mail.mailers.smtp.encryption' => $validated['mail_encryption'],
                'mail.from.address'            => $validated['mail_from_address'],
                'mail.from.name'               => $validated['mail_from_name'],
            ]);

            // Kirim email test menggunakan template profesional
            \Illuminate\Support\Facades\Mail::send('emails.test-mail', [
                'userName'       => Auth::user()->name,
                'mailHost'       => $validated['mail_host'],
                'mailPort'       => $validated['mail_port'],
                'mailEncryption' => $validated['mail_encryption'],
            ], function ($message) {
                $message->to(Auth::user()->email)
                        ->subject('Test Koneksi SMTP');
            });

            return response()->json([
                'success' => true,
                'message' => 'Koneksi SMTP Berhasil! Email uji coba telah dikirim ke: ' . Auth::user()->email
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail Test Failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke SMTP: ' . $e->getMessage()
            ], 500);
        }
    }
}
