<?php

namespace Database\Seeders;

use App\Modules\SSO\Models\SsoClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SsoClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name'           => 'Portal Akademik App',
                'webhook_url'    => 'https://akademik.campus.test/api/sso/webhook',
                'webhook_secret' => 'whsec_' . bin2hex(random_bytes(16)),
                'is_active'      => true,
            ],
            [
                'name'           => 'E-Learning LMS',
                'webhook_url'    => 'https://elearning.campus.test/api/sso/webhook',
                'webhook_secret' => 'whsec_' . bin2hex(random_bytes(16)),
                'is_active'      => true,
            ],
            [
                'name'           => 'HRIS Core System',
                'webhook_url'    => 'https://hr.campus.test/api/sso/webhook',
                'webhook_secret' => 'whsec_' . bin2hex(random_bytes(16)),
                'is_active'      => true,
            ],
        ];

        if (class_exists(SsoClient::class)) {
            foreach ($clients as $client) {
                // Dalam skenario asli, oauth_client_id akan merujuk ke oauth_clients hasil generate passport.
                if (Schema::hasTable('oauth_clients')) {
                    $clientId = Str::uuid()->toString();
                    
                    DB::table('oauth_clients')->insert([
                        'id'                     => $clientId,
                        'name'                   => $client['name'],
                        'secret'                 => bin2hex(random_bytes(20)),
                        'provider'               => 'users',
                        'redirect_uris'          => 'http://localhost/auth/callback',
                        'grant_types'            => 'authorization_code',
                        'revoked'                => false,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);
                    
                    $client['oauth_client_id'] = $clientId;
                    SsoClient::firstOrCreate(['name' => $client['name']], $client);
                } else {
                    $this->command->warn('Tabel oauth_clients belum ada, seeder SsoClient dilewati.');
                    break;
                }
            }
        } else {
            $this->command->warn('Model SsoClient belum dibuat, seeder dilewati.');
        }
    }
}
