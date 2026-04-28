<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoginLogFactory extends Factory
{
    private static array $decisions = ['ALLOW', 'ALLOW', 'ALLOW', 'OTP', 'BLOCK'];
    private static array $statuses  = ['success', 'success', 'success', 'otp_required', 'blocked', 'failed'];
    private static array $flags     = [
        'new_device_detected', 'new_country_detected', 'vpn_usage',
        'high_risk_ip', 'failed_attempts:2', 'failed_attempts:3',
        'abnormal_login_hour', 'high_request_speed', 'low_device_trust',
        'ai_fallback_active',
    ];
    private static array $countries = ['ID', 'SG', 'MY', 'US', 'GB', 'AU', 'JP', 'DE', 'NL', 'INTERNAL'];

    public function definition(): array
    {
        $decision = fake()->randomElement(self::$decisions);
        $status   = match ($decision) {
            'ALLOW' => 'success',
            'OTP'   => fake()->boolean(60) ? 'otp_required' : 'success',
            'BLOCK' => 'blocked',
            default => 'failed',
        };

        $riskScore = match ($decision) {
            'ALLOW' => fake()->numberBetween(0, 29),
            'OTP'   => fake()->numberBetween(30, 59),
            'BLOCK' => fake()->numberBetween(60, 100),
            default => fake()->numberBetween(0, 100),
        };

        $flagCount   = fake()->numberBetween(0, 3);
        $reasonFlags = $flagCount > 0
            ? fake()->randomElements(self::$flags, $flagCount)
            : [];

        return [
            'user_id'            => null, // di-set oleh seeder
            'email_attempted'    => fake()->safeEmail(),
            'ip_address'         => fake()->ipv4(),
            'device_fingerprint' => fake()->optional(0.8)->sha256(),
            'user_agent'         => fake()->userAgent(),
            'country_code'       => fake()->randomElement(self::$countries),
            'risk_score'         => $riskScore,
            'decision'           => $decision,
            'reason_flags'       => json_encode($reasonFlags),
            'ai_response_raw'    => null,
            'status'             => $status,
            'occurred_at'        => fake()->dateTimeBetween('-1 year')->format('Y-m-d H:i:s'),
        ];
    }
}
