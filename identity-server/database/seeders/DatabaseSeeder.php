<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name'              => 'lazuardi Mandegar',
                'email'             => 'lazamediamxt@gmail.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
            ],
            [
                'name'              => 'lazzamart357',
                'email'             => 'lazamart357@gmail.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
            ],
            [
                'name'              => 'Ahmad Fauzi',
                'email'             => 'ahmad.fauzi@gmail.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
            ],
            [
                'name'              => 'Dewi Lestari',
                'email'             => 'dewi.lestari@gmail.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
            ],
            [
                'name'              => 'Rizky Pratama',
                'email'             => 'rizky.pratama@gmail.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }

        // Seed roles, permissions, dan assign ke users
        $this->call([
            RolePermissionSeeder::class,
            UserRoleSeeder::class,
            AccessAreaSeeder::class,
            SsoClientSeeder::class,
            // BigDataSeeder::class,
        ]);
    }
}