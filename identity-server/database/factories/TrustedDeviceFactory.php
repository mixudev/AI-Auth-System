<?php
// ============================================================
// database/factories/TrustedDeviceFactory.php
// ============================================================
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrustedDeviceFactory extends Factory
{
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-1 year');
        return [
            'user_id'          => null,
            'fingerprint_hash' => fake()->sha256(),
            'device_label'     => fake()->randomElement([
                'Chrome on Windows', 'Firefox on Mac', 'Safari on iPhone',
                'Chrome on Android', 'Edge on Windows', 'Safari on Mac',
            ]),
            'ip_address'       => fake()->ipv4(),
            'country_code'     => fake()->randomElement(['ID','SG','MY','US','GB']),
            'last_seen_at'     => fake()->dateTimeBetween($createdAt)->format('Y-m-d H:i:s'),
            'trusted_until'    => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d H:i:s'),
            'is_revoked'       => fake()->boolean(10),
            'created_at'       => $createdAt->format('Y-m-d H:i:s'),
            'updated_at'       => now()->format('Y-m-d H:i:s'),
        ];
    }
}
