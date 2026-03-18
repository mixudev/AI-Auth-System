<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /*
    |--------------------------------------------------------------------------
    | Factory untuk menghasilkan data pengguna uji.
    | Password di-hash menggunakan Argon2id melalui config hashing.
    |--------------------------------------------------------------------------
    */

    // Gunakan satu password yang di-hash sekali untuk efisiensi
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => 'Test User',
            'email'             => 'test_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'is_active'         => true,
            'last_login_at'     => null,
            'last_login_ip'     => null,
        ];
    }

    /**
     * State untuk pengguna yang belum memverifikasi email.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State untuk pengguna yang dinonaktifkan.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
