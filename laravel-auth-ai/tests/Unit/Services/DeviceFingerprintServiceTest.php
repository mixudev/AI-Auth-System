<?php

namespace Tests\Unit\Services;

use App\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Tests\TestCase;

class DeviceFingerprintServiceTest extends TestCase
{
    private DeviceFingerprintService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DeviceFingerprintService();
    }

    /**
     * Request yang identik menghasilkan fingerprint yang sama (deterministik).
     */
    public function test_same_request_produces_same_fingerprint(): void
    {
        $request = $this->buildRequest(
            userAgent: 'Mozilla/5.0 (Windows NT 10.0) Chrome/120',
            acceptLanguage: 'id-ID,id;q=0.9',
        );

        $fp1 = $this->service->generate($request);
        $fp2 = $this->service->generate($request);

        $this->assertSame($fp1, $fp2);
    }

    /**
     * Request dengan User-Agent berbeda menghasilkan fingerprint berbeda.
     */
    public function test_different_user_agents_produce_different_fingerprints(): void
    {
        $request1 = $this->buildRequest(userAgent: 'Chrome/120');
        $request2 = $this->buildRequest(userAgent: 'Firefox/120');

        $fp1 = $this->service->generate($request1);
        $fp2 = $this->service->generate($request2);

        $this->assertNotSame($fp1, $fp2);
    }

    /**
     * Fingerprint selalu berupa string SHA-256 (64 karakter hex).
     */
    public function test_fingerprint_is_sha256_hash(): void
    {
        $request     = $this->buildRequest();
        $fingerprint = $this->service->generate($request);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $fingerprint);
    }

    /**
     * Metode matches() bekerja dengan benar.
     */
    public function test_matches_returns_true_for_same_fingerprint(): void
    {
        $request = $this->buildRequest(userAgent: 'TestBrowser/1.0');
        $stored  = $this->service->generate($request);

        $this->assertTrue($this->service->matches($request, $stored));
    }

    /**
     * Label perangkat dibangun dengan benar dari User-Agent.
     */
    public function test_device_label_detects_browser_and_os(): void
    {
        $request = $this->buildRequest(
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0'
        );

        $label = $this->service->buildDeviceLabel($request);

        $this->assertStringContainsString('Chrome', $label);
        $this->assertStringContainsString('Windows', $label);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    private function buildRequest(
        string $userAgent = 'TestAgent/1.0',
        string $acceptLanguage = 'en-US',
    ): Request {
        $request = Request::create('/login', 'POST');
        $request->headers->set('User-Agent', $userAgent);
        $request->headers->set('Accept-Language', $acceptLanguage);

        return $request;
    }
}
