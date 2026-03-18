<?php

namespace Tests\Unit\Services;

use App\Services\RiskFallbackService;
use Tests\TestCase;

class RiskFallbackServiceTest extends TestCase
{
    private RiskFallbackService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RiskFallbackService();
    }

    /**
     * Sinyal rendah → keputusan ALLOW.
     */
    public function test_low_risk_signals_produce_allow(): void
    {
        $payload = $this->buildPayload(
            isNewDevice: false,
            isNewCountry: false,
            isVpn: false,
            failedAttempts: 0,
            loginHour: 10,
            ipRiskScore: 0,
        );

        $result = $this->service->assess($payload);

        $this->assertSame('ALLOW', $result->decision);
        $this->assertTrue($result->isFallback);
        $this->assertContains('fallback_mode', $result->reasonFlags);
    }

    /**
     * Kombinasi sinyal sedang → keputusan OTP.
     */
    public function test_medium_risk_signals_produce_otp(): void
    {
        $payload = $this->buildPayload(
            isNewDevice: true,
            isNewCountry: false,
            isVpn: false,
            failedAttempts: 1,
            loginHour: 10,
            ipRiskScore: 10,
        );

        $result = $this->service->assess($payload);

        // Perangkat baru (25) + 1 percobaan gagal (5) + ip_risk (4) = 34 → OTP
        $this->assertSame('OTP', $result->decision);
        $this->assertContains('new_device', $result->reasonFlags);
    }

    /**
     * Sinyal tinggi → keputusan BLOCK.
     */
    public function test_high_risk_signals_produce_block(): void
    {
        $payload = $this->buildPayload(
            isNewDevice: true,
            isNewCountry: true,
            isVpn: true,
            failedAttempts: 4,
            loginHour: 3,   // Jam mencurigakan
            ipRiskScore: 60,
        );

        $result = $this->service->assess($payload);

        $this->assertSame('BLOCK', $result->decision);
        $this->assertGreaterThanOrEqual(60, $result->riskScore);
    }

    /**
     * Skor tidak boleh melebihi 100.
     */
    public function test_risk_score_is_capped_at_100(): void
    {
        $payload = $this->buildPayload(
            isNewDevice: true,
            isNewCountry: true,
            isVpn: true,
            failedAttempts: 10,
            loginHour: 2,
            ipRiskScore: 100,
        );

        $result = $this->service->assess($payload);

        $this->assertLessThanOrEqual(100, $result->riskScore);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    private function buildPayload(
        bool $isNewDevice,
        bool $isNewCountry,
        bool $isVpn,
        int  $failedAttempts,
        int  $loginHour,
        int  $ipRiskScore,
    ): array {
        return [
            'is_new_device'    => $isNewDevice,
            'is_new_country'   => $isNewCountry,
            'is_vpn'           => $isVpn,
            'failed_attempts'  => $failedAttempts,
            'login_hour'       => $loginHour,
            'ip_risk_score'    => $ipRiskScore,
            'request_speed'    => 1.0,
        ];
    }
}
