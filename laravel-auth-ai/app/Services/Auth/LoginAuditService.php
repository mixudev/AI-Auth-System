<?php

namespace App\Services\Auth;

use App\DTOs\RiskAssessmentResult;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\Security\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginAuditService
{
    /*
    |--------------------------------------------------------------------------
    | Layanan untuk mencatat semua aktivitas login ke database dan log file.
    |
    | Setiap keputusan BLOCK harus memiliki alasan yang dapat dijelaskan.
    | Digunakan untuk investigasi insiden dan pelaporan keamanan.
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    /**
     * Catat percobaan login yang berhasil melewati semua pemeriksaan.
     */
    public function recordSuccess(
        Request              $request,
        User                 $user,
        RiskAssessmentResult $result
    ): void {
        $this->writeLog($request, $user, $result, LoginLog::STATUS_SUCCESS);

        Log::channel('security')->info('Login berhasil', [
            'user_id'    => $user->id,
            'risk_score' => $result->riskScore,
            'ip_address' => $this->fingerprintService->getRealIp($request),
        ]);
    }

    /**
     * Catat percobaan login yang memerlukan verifikasi OTP.
     */
    public function recordOtpRequired(
        Request              $request,
        User                 $user,
        RiskAssessmentResult $result
    ): LoginLog {
        $log = $this->writeLog($request, $user, $result, LoginLog::STATUS_OTP);

        Log::channel('security')->info('Login memerlukan OTP', [
            'user_id'     => $user->id,
            'risk_score'  => $result->riskScore,
            'reason_flags' => $result->reasonFlags,
        ]);

        return $log;
    }

    /**
     * Catat percobaan login yang diblokir karena risiko tinggi.
     * Setiap pemblokiran HARUS menyertakan reason_flags yang dapat dijelaskan.
     */
    public function recordBlocked(
        Request              $request,
        User                 $user,
        RiskAssessmentResult $result
    ): void {
        $this->writeLog($request, $user, $result, LoginLog::STATUS_BLOCKED);

        // Log level WARNING agar mudah dideteksi oleh sistem monitoring
        Log::channel('security')->warning('Login DIBLOKIR', [
            'user_id'      => $user->id,
            'risk_score'   => $result->riskScore,
            'reason_flags' => $result->reasonFlags,
            'ip_address'   => $this->fingerprintService->getRealIp($request),
            'is_fallback'  => $result->isFallback,
        ]);
    }

    /**
     * Catat bahwa user berhasil login setelah melewati verifikasi OTP.
     * Digunakan untuk mengakhiri flow OTP dengan status SUCCESS.
     */
    public function recordOtpSuccess(Request $request, User $user, ?int $logId = null): void
    {
        // Jika ada logId, update baris yang sudah ada alih-alih membuat baris baru
        if ($logId) {
            $existingLog = LoginLog::find($logId);
            if ($existingLog) {
                $existingLog->update([
                    'status'       => LoginLog::STATUS_SUCCESS,
                    'reason_flags' => array_unique(array_merge($existingLog->reason_flags ?? [], ['otp_verified'])),
                    'occurred_at'  => now(),
                ]);
                return;
            }
        }

        // Jika tidak ada logId, kita jangan membuat baris baru 
        // untuk menghindari duplikasi jika recordSuccess() sudah dipanggil oleh handleAllowDecision
    }

    /**
     * Catat percobaan login yang gagal karena password salah.
     */
    public function recordFailedPassword(Request $request, string $emailAttempted): void
    {
        $ip = $this->fingerprintService->getRealIp($request);
        
        LoginLog::create([
            'user_id'            => null,
            'email_attempted'    => $emailAttempted,
            'ip_address'         => $ip,
            'device_fingerprint' => $this->fingerprintService->generate($request),
            'user_agent'         => $request->userAgent(),
            'country_code'       => $this->fingerprintService->getCountry($ip),
            'risk_score'         => null,
            'decision'           => null,
            'reason_flags'       => ['wrong_password'],
            'status'             => LoginLog::STATUS_FAILED,
            'occurred_at'        => now(),
        ]);
    }

    /**
     * Tulis rekaman log ke database.
     */
    private function writeLog(
        Request              $request,
        User                 $user,
        RiskAssessmentResult $result,
        string               $status
    ): LoginLog {
        $ip = $this->fingerprintService->getRealIp($request);

        return LoginLog::create([
            'user_id'            => $user->id,
            'email_attempted'    => $user->email,
            'ip_address'         => $ip,
            'device_fingerprint' => $this->fingerprintService->generate($request),
            'user_agent'         => $request->userAgent(),
            'country_code'       => $this->fingerprintService->getCountry($ip),
            'risk_score'         => $result->riskScore,
            'decision'           => $result->decision,
            'reason_flags'       => $result->reasonFlags,
            'ai_response_raw'    => array_merge((array)$result->rawResponse, ['_inputs' => $result->payload]),
            'status'             => $status,
            'occurred_at'        => now(),
        ]);
    }
}
