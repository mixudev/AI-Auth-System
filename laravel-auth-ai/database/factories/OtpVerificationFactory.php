<?php
// ============================================================
// database/factories/OtpVerificationFactory.php
// ============================================================
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class OtpVerificationFactory extends Factory
{
    public function definition(): array
    {
        $createdAt  = fake()->dateTimeBetween('-3 months');
        $expiresAt  = (clone $createdAt)->modify('+5 minutes');
        $isVerified = fake()->boolean(60);
        $isExpired  = !$isVerified && fake()->boolean(50);

        return [
            'user_id'            => null,
            'token'              => Hash::make((string) fake()->numberBetween(100000, 999999)),
            'session_token_hash' => fake()->unique()->sha256(),
            'ip_address'         => fake()->ipv4(),
            'device_fingerprint' => fake()->optional(0.8)->sha256(),
            'expires_at'         => $isExpired
                                    ? (clone $createdAt)->modify('-1 minute')->format('Y-m-d H:i:s')
                                    : $expiresAt->format('Y-m-d H:i:s'),
            'attempts'           => fake()->numberBetween(0, 3),
            'verified_at'        => $isVerified
                                    ? (clone $createdAt)->modify('+2 minutes')->format('Y-m-d H:i:s')
                                    : null,
            'created_at'         => $createdAt->format('Y-m-d H:i:s'),
            'updated_at'         => now()->format('Y-m-d H:i:s'),
        ];
    }
}
