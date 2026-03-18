<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        static $password = null;
        $password ??= Hash::make('password'); // hash sekali, reuse

        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => fake()->optional(0.8)->dateTimeBetween('-2 years'),
            'password'          => $password,
            'is_active'         => fake()->boolean(90),
            'last_login_at'     => fake()->optional(0.7)->dateTimeBetween('-6 months'),
            'last_login_ip'     => fake()->optional(0.7)->ipv4(),
            'remember_token'    => null,
            'created_at'        => fake()->dateTimeBetween('-2 years'),
            'updated_at'        => now(),
            'deleted_at'        => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
