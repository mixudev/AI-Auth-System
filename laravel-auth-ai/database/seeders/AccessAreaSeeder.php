<?php

namespace Database\Seeders;

use App\Modules\SSO\Models\AccessArea;
use Illuminate\Database\Seeder;

class AccessAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'name'        => 'Academic Portal',
                'slug'        => 'academic-portal',
                'description' => 'Sistem Informasi Akademik untuk mahasiswa dan dosen',
                'is_active'   => true,
            ],
            [
                'name'        => 'Finance Hub',
                'slug'        => 'finance-hub',
                'description' => 'Sistem Pembayaran dan Keuangan Mahasiswa',
                'is_active'   => true,
            ],
            [
                'name'        => 'E-Learning Campus',
                'slug'        => 'e-learning-campus',
                'description' => 'LMS dan Pembelajaran Online',
                'is_active'   => true,
            ],
            [
                'name'        => 'HRIS Core',
                'slug'        => 'hris-core',
                'description' => 'Sistem Manajemen SDM dan Kepegawaian',
                'is_active'   => true,
            ],
            [
                'name'        => 'Library Access',
                'slug'        => 'library-access',
                'description' => 'Akses ke Sistem Perpustakaan Terpadu',
                'is_active'   => true,
            ]
        ];

        // Hanya jika modelnya sudah ada
        if (class_exists(AccessArea::class)) {
            foreach ($areas as $area) {
                AccessArea::firstOrCreate(['slug' => $area['slug']], $area);
            }
        } else {
            $this->command->warn('Model AccessArea belum dibuat, seeder dilewati.');
        }
    }
}
