<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\OtpVerification;
use App\Models\TrustedDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DEV ONLY — Hapus atau proteksi controller ini sebelum production!
 */
class DevMonitoringController extends Controller
{
    public function dashboard()
    {
        return view('dev.monitoring');
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'users'           => User::count(),
            'active_users'    => User::where('is_active', true)->count(),
            'total_logs'      => LoginLog::count(),
            'blocked_logs'    => LoginLog::where('decision', 'BLOCK')->count(),
            'active_otps'     => OtpVerification::where('expires_at', '>', now())
                                    ->whereNull('verified_at')
                                    ->count(),
            'trusted_devices' => TrustedDevice::where('is_revoked', false)->count(),
        ]);
    }

    public function otps(): JsonResponse
    {
        $otps = OtpVerification::with('user:id,name,email')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($otp) => [
                'id'          => $otp->id,
                'user'        => $otp->user?->name ?? '—',
                'email'       => $otp->user?->email ?? '—',
                'otp_code'    => $otp->token,
                'status'      => $otp->verified_at
                                    ? 'verified'
                                    : ($otp->expires_at < now() ? 'expired' : 'active'),
                'attempts'    => $otp->attempts ?? 0,
                'expires_at'  => $otp->expires_at?->toDateTimeString(),
                'verified_at' => $otp->verified_at?->toDateTimeString(),
                'created_at'  => $otp->created_at->toDateTimeString(),
            ]);

        return response()->json($otps);
    }

    public function loginLogs(): JsonResponse
    {
        $logs = LoginLog::with('user:id,name,email')
            ->orderBy('occurred_at', 'desc')
            ->limit(100)
            ->get()
            ->map(fn($log) => [
                'id'           => $log->id,
                'user'         => $log->user?->name ?? 'Unknown',
                'email'        => $log->user?->email ?? $log->email_attempted ?? '—',
                'ip_address'   => $log->ip_address,
                'country_code' => $log->country_code,
                'user_agent'   => $log->user_agent,
                'device_fp'    => $log->device_fingerprint
                                    ? substr($log->device_fingerprint, 0, 16) . '...'
                                    : '—',
                'status'       => $log->status,
                'decision'     => $log->decision ?? '—',
                'risk_score'   => $log->risk_score,
                'reason_flags' => $log->reason_flags,
                'is_fallback'  => $log->status === 'fallback',
                'occurred_at'  => $log->occurred_at->toDateTimeString(),
            ]);

        return response()->json($logs);
    }

    public function trustedDevices(): JsonResponse
    {
        $devices = TrustedDevice::with('user:id,name,email')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn($device) => [
                'id'            => $device->id,
                'user'          => $device->user?->name ?? '—',
                'email'         => $device->user?->email ?? '—',
                'fingerprint'   => substr($device->fingerprint_hash, 0, 16) . '...',
                'device_label'  => $device->device_label ?? '—',
                'ip_address'    => $device->ip_address,
                'country_code'  => $device->country_code,
                'is_revoked'    => $device->is_revoked,
                'last_seen'     => $device->last_seen_at?->toDateTimeString() ?? '—',
                'trusted_until' => $device->trusted_until?->toDateTimeString() ?? '—',
                'created_at'    => $device->created_at->toDateTimeString(),
            ]);

        return response()->json($devices);
    }

    public function users(): JsonResponse
    {
        $users = User::withCount(['loginLogs', 'trustedDevices'])
            ->latest()
            ->get()
            ->map(fn($user) => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'is_active'     => $user->is_active,
                'verified'      => !is_null($user->email_verified_at),
                'last_login_at' => $user->last_login_at?->toDateTimeString() ?? '—',
                'last_login_ip' => $user->last_login_ip ?? '—',
                'login_count'   => $user->login_logs_count,
                'device_count'  => $user->trusted_devices_count,
                'is_blocked'    => $this->isUserBlocked($user->id),
                'created_at'    => $user->created_at->toDateTimeString(),
            ]);

        return response()->json($users);
    }

    /**
     * Unblock user: clear cache + hapus block logs terbaru + restore devices.
     */
    public function unblockUser(int $userId): JsonResponse
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($user) {
            // 1. Clear semua cache failed attempts
            $keys = Cache::get("failed_attempt_ips:{$user->id}", []);
            foreach ($keys as $ip) {
                Cache::forget("failed_attempts:{$user->id}:{$ip}");
            }
            Cache::forget("failed_attempt_ips:{$user->id}");
            Cache::forget("risk_score:{$user->id}");
            Cache::forget("login_blocked:{$user->id}");

            // 2. Hapus 10 login log BLOCK terbaru (tidak hapus semua untuk audit trail)
            LoginLog::where('user_id', $user->id)
                ->where('decision', LoginLog::DECISION_BLOCK)
                ->orderBy('occurred_at', 'desc')
                ->limit(10)
                ->get()
                ->each->delete();

            // 3. Restore semua trusted devices yang ter-revoke
            TrustedDevice::where('user_id', $user->id)
                ->where('is_revoked', true)
                ->update(['is_revoked' => false]);

            // 4. Pastikan akun aktif
            $user->update(['is_active' => true]);
        });

        Log::channel('security')->info('User di-unblock via DEV monitoring', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} ({$user->email}) berhasil di-unblock.",
        ]);
    }

    /**
     * Toggle revoke/restore sebuah trusted device.
     */
    public function revokeDevice(int $deviceId): JsonResponse
    {
        $device = TrustedDevice::with('user:id,name,email')->find($deviceId);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan.'], 404);
        }

        $newState = !$device->is_revoked;
        $device->update(['is_revoked' => $newState]);
        $action = $newState ? 'direvoke' : 'di-restore';

        Log::channel('security')->info("Trusted device {$action} via DEV monitoring", [
            'device_id' => $deviceId,
            'user_id'   => $device->user_id,
        ]);

        return response()->json([
            'success'    => true,
            'is_revoked' => $newState,
            'message'    => "Device {$action} untuk user {$device->user?->name}.",
        ]);
    }

    // -----------------------------------------------------------------------
    // Private Helpers
    // -----------------------------------------------------------------------

    private function isUserBlocked(int $userId): bool
    {
        $lastLog = LoginLog::where('user_id', $userId)
            ->orderBy('occurred_at', 'desc')
            ->first();

        return $lastLog?->decision === LoginLog::DECISION_BLOCK;
    }
}