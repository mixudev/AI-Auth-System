<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BigDataSeeder extends Seeder
{
    /*
    |--------------------------------------------------------------------------
    | Big Data Seeder — pure PHP, tanpa Faker dependency
    | Target: ~1 juta login_logs + semua tabel terkait
    |--------------------------------------------------------------------------
    */

    private const USERS        = 10_000;
    private const LOGIN_LOGS   = 1_000_000;
    private const DEVICES      = 30_000;
    private const OTP          = 50_000;
    private const IP_BLACKLIST = 500;
    private const IP_WHITELIST = 50;
    private const USER_BLOCKS  = 1_000;
    private const CHUNK        = 1_000;

    // ── Data pool statis ──────────────────────────────────────────────────
    private array $userIds      = [];
    private array $ips          = [];
    private array $fingerprints = [];

    private array $firstNames = [
        'Budi','Siti','Ahmad','Dewi','Rudi','Rina','Hendra','Maya','Agus','Lina',
        'Dani','Novi','Fajar','Wulan','Rizky','Indah','Eko','Sari','Wahyu','Dian',
        'Bagas','Fitri','Gilang','Hesti','Ivan','Joko','Kartika','Lutfi','Mira','Nanda',
    ];
    private array $lastNames = [
        'Santoso','Wijaya','Kusuma','Pratama','Saputra','Hidayat','Nugroho','Susanto',
        'Wibowo','Hartono','Setiawan','Raharjo','Utomo','Purnomo','Gunawan','Halim',
        'Kurniawan','Firmansyah','Hakim','Iskandar','Junaedi','Lesmana','Mahendra',
    ];
    private array $domains = [
        'gmail.com','yahoo.com','hotmail.com','outlook.com','icloud.com',
        'company.co.id','email.com','mail.com','proton.me',
    ];
    private array $decisions    = ['ALLOW','ALLOW','ALLOW','ALLOW','OTP','OTP','BLOCK'];
    private array $countries    = ['ID','ID','ID','ID','SG','MY','US','GB','AU','JP','DE'];
    private array $flagPool     = [
        'new_device_detected','new_country_detected','vpn_usage','high_risk_ip',
        'failed_attempts:2','failed_attempts:3','abnormal_login_hour',
        'high_request_speed','low_device_trust','ai_fallback_active',
    ];
    private array $userAgents   = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
    ];
    private array $deviceLabels = [
        'Chrome on Windows','Firefox on Mac','Safari on iPhone',
        'Chrome on Android','Edge on Windows','Safari on Mac','Chrome on Linux',
    ];
    private array $blockReasons = [
        'Auto-lock: 3 keputusan BLOCK dalam 24 jam',
        'Manual block via monitoring',
        'Suspicious activity detected',
        'Account compromise suspected',
        'Brute force detected',
    ];
    private array $ipBlockReasons = [
        'Auto-block: 5 keputusan BLOCK dalam 24 jam',
        'Manual block via monitoring',
        'Brute force detected',
        'Known malicious IP',
        'VPN abuse detected',
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════╗');
        $this->command->info('║       BIG DATA SEEDER — START        ║');
        $this->command->info('╚══════════════════════════════════════╝');
        $this->command->info('');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('SET unique_checks=0;');
        DB::statement('SET autocommit=0;');

        $totalStart = microtime(true);

        $this->seedUsers();
        $this->buildPools();
        $this->seedIpWhitelist();
        $this->seedIpBlacklist();
        $this->seedLoginLogs();
        $this->seedTrustedDevices();
        $this->seedOtpVerifications();
        $this->seedUserBlocks();

        DB::statement('COMMIT;');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::statement('SET unique_checks=1;');
        DB::statement('SET autocommit=1;');

        $elapsed = round(microtime(true) - $totalStart, 1);
        $this->command->info('');
        $this->command->info("✓ Semua selesai dalam {$elapsed} detik.");
        $this->command->info('');
    }

    // ── Users ──────────────────────────────────────────────────────────────

    private function seedUsers(): void
    {
        $this->command->info('→ Seeding users (' . number_format(self::USERS) . ')...');
        $start    = microtime(true);
        $password = Hash::make('password');
        $chunk    = [];
        $emails   = [];

        // Akun demo yang selalu ada
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@demo.com'],
            [
                'name'              => 'Admin Demo',
                'password'          => $password,
                'is_active'         => true,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
                'deleted_at'        => null,
            ]
        );

        for ($i = 0; $i < self::USERS; $i++) {
            // Generate email unik
            do {
                $first = $this->firstNames[array_rand($this->firstNames)];
                $last  = $this->lastNames[array_rand($this->lastNames)];
                $email = strtolower($first . '.' . $last . mt_rand(1, 99999))
                         . '@' . $this->domains[array_rand($this->domains)];
            } while (isset($emails[$email]));
            $emails[$email] = true;

            $createdAt = $this->randDate('-2 years');
            $chunk[] = [
                'name'              => $first . ' ' . $last,
                'email'             => $email,
                'email_verified_at' => mt_rand(0,9) < 8 ? $this->randDate('-2 years') : null,
                'password'          => $password,
                'is_active'         => mt_rand(0,9) > 0,
                'last_login_at'     => mt_rand(0,9) < 7 ? $this->randDate('-6 months') : null,
                'last_login_ip'     => mt_rand(0,9) < 7 ? $this->randIp() : null,
                'remember_token'    => null,
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
                'deleted_at'        => null,
            ];

            if (count($chunk) >= self::CHUNK) {
                DB::table('users')->insert($chunk);
                $chunk = [];
            }
        }
        if ($chunk) DB::table('users')->insert($chunk);
        DB::statement('COMMIT;');

        $this->userIds = DB::table('users')->pluck('id')->toArray();
        $this->printDone('users', count($this->userIds), $start);
    }

    // ── Login Logs ─────────────────────────────────────────────────────────

    private function seedLoginLogs(): void
    {
        $this->command->info('→ Seeding login_logs (' . number_format(self::LOGIN_LOGS) . ')...');
        $start     = microtime(true);
        $chunk     = [];
        $userCount = count($this->userIds);
        $ipCount   = count($this->ips);
        $fpCount   = count($this->fingerprints);
        $uaCount   = count($this->userAgents);
        $decCount  = count($this->decisions);
        $ctCount   = count($this->countries);
        $flCount   = count($this->flagPool);

        for ($i = 0; $i < self::LOGIN_LOGS; $i++) {
            $decision  = $this->decisions[mt_rand(0, $decCount - 1)];
            $riskScore = match ($decision) {
                'ALLOW' => mt_rand(0, 29),
                'OTP'   => mt_rand(30, 59),
                'BLOCK' => mt_rand(60, 100),
            };
            $status = match ($decision) {
                'ALLOW' => 'success',
                'OTP'   => mt_rand(0,1) ? 'otp_required' : 'success',
                'BLOCK' => 'blocked',
            };

            $flagCount = mt_rand(0, 3);
            $flags = [];
            if ($flagCount > 0) {
                $startIdx = mt_rand(0, $flCount - 1);
                for ($f = 0; $f < $flagCount; $f++) {
                    $flags[] = $this->flagPool[($startIdx + $f) % $flCount];
                }
                $flags = array_unique($flags);
            }

            $chunk[] = [
                'user_id'            => $this->userIds[mt_rand(0, $userCount - 1)],
                'email_attempted'    => $this->randEmail(),
                'ip_address'         => $this->ips[mt_rand(0, $ipCount - 1)],
                'device_fingerprint' => mt_rand(0,9) < 8 ? $this->fingerprints[mt_rand(0, $fpCount - 1)] : null,
                'user_agent'         => $this->userAgents[mt_rand(0, $uaCount - 1)],
                'country_code'       => $this->countries[mt_rand(0, $ctCount - 1)],
                'risk_score'         => $riskScore,
                'decision'           => $decision,
                'reason_flags'       => json_encode(array_values($flags)),
                'ai_response_raw'    => null,
                'status'             => $status,
                'occurred_at'        => $this->randDate('-1 year'),
            ];

            if (count($chunk) >= self::CHUNK) {
                DB::table('login_logs')->insert($chunk);
                $chunk = [];
                DB::statement('COMMIT;');
            }

            if ($i > 0 && $i % 50_000 === 0) {
                $pct     = round($i / self::LOGIN_LOGS * 100);
                $elapsed = round(microtime(true) - $start, 1);
                $this->command->info("  {$pct}% — " . number_format($i) . " rows ({$elapsed}s)");
            }
        }
        if ($chunk) { DB::table('login_logs')->insert($chunk); DB::statement('COMMIT;'); }

        $this->printDone('login_logs', self::LOGIN_LOGS, $start);
    }

    // ── Trusted Devices ────────────────────────────────────────────────────

    private function seedTrustedDevices(): void
    {
        $this->command->info('→ Seeding trusted_devices (' . number_format(self::DEVICES) . ')...');
        $start     = microtime(true);
        $chunk     = [];
        $userCount = count($this->userIds);
        $ipCount   = count($this->ips);
        $fpCount   = count($this->fingerprints);
        $dlCount   = count($this->deviceLabels);
        $ctCount   = count($this->countries);
        $seen      = [];
        $inserted  = 0;

        for ($attempt = 0; $attempt < self::DEVICES * 2 && $inserted < self::DEVICES; $attempt++) {
            $userId = $this->userIds[mt_rand(0, $userCount - 1)];
            $fp     = $this->fingerprints[mt_rand(0, $fpCount - 1)];
            $key    = "{$userId}:{$fp}";
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $createdAt = $this->randDate('-1 year');
            $chunk[] = [
                'user_id'          => $userId,
                'fingerprint_hash' => $fp,
                'device_label'     => $this->deviceLabels[mt_rand(0, $dlCount - 1)],
                'ip_address'       => $this->ips[mt_rand(0, $ipCount - 1)],
                'country_code'     => $this->countries[mt_rand(0, $ctCount - 1)],
                'last_seen_at'     => $this->randDate('-1 month'),
                'trusted_until'    => date('Y-m-d H:i:s', time() + 86400 * mt_rand(1, 30)),
                'is_revoked'       => mt_rand(0, 9) === 0,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ];
            $inserted++;

            if (count($chunk) >= self::CHUNK) {
                DB::table('trusted_devices')->insert($chunk);
                $chunk = [];
                DB::statement('COMMIT;');
            }
        }
        if ($chunk) { DB::table('trusted_devices')->insert($chunk); DB::statement('COMMIT;'); }
        $this->printDone('trusted_devices', $inserted, $start);
    }

    // ── OTP Verifications ──────────────────────────────────────────────────

    private function seedOtpVerifications(): void
    {
        $this->command->info('→ Seeding otp_verifications (' . number_format(self::OTP) . ')...');
        $start     = microtime(true);
        $chunk     = [];
        $userCount = count($this->userIds);
        $ipCount   = count($this->ips);
        $fpCount   = count($this->fingerprints);
        $token     = Hash::make('123456');

        for ($i = 0; $i < self::OTP; $i++) {
            $createdTs  = mt_rand(strtotime('-3 months'), time());
            $isVerified = mt_rand(0,9) < 6;
            $isExpired  = !$isVerified && mt_rand(0,1);
            $expiresTs  = $isExpired ? $createdTs - 60 : $createdTs + 300;

            $chunk[] = [
                'user_id'            => $this->userIds[mt_rand(0, $userCount - 1)],
                'token'              => $token,
                'session_token'      => bin2hex(random_bytes(32)),
                'ip_address'         => $this->ips[mt_rand(0, $ipCount - 1)],
                'device_fingerprint' => mt_rand(0,9) < 8 ? $this->fingerprints[mt_rand(0, $fpCount - 1)] : null,
                'expires_at'         => date('Y-m-d H:i:s', $expiresTs),
                'attempts'           => mt_rand(0, 3),
                'verified_at'        => $isVerified ? date('Y-m-d H:i:s', $createdTs + mt_rand(30,120)) : null,
                'created_at'         => date('Y-m-d H:i:s', $createdTs),
                'updated_at'         => date('Y-m-d H:i:s', $createdTs),
            ];

            if (count($chunk) >= self::CHUNK) {
                DB::table('otp_verifications')->insert($chunk);
                $chunk = [];
                DB::statement('COMMIT;');
            }
        }
        if ($chunk) { DB::table('otp_verifications')->insert($chunk); DB::statement('COMMIT;'); }
        $this->printDone('otp_verifications', self::OTP, $start);
    }

    // ── IP Blacklist ───────────────────────────────────────────────────────

    private function seedIpBlacklist(): void
    {
        $this->command->info('→ Seeding ip_blacklist (' . number_format(self::IP_BLACKLIST) . ')...');
        $start  = microtime(true);
        $chunk  = [];
        $usedIp = [];
        $rCount = count($this->ipBlockReasons);

        for ($i = 0; $i < self::IP_BLACKLIST; $i++) {
            do { $ip = $this->randIp(); } while (isset($usedIp[$ip]));
            $usedIp[$ip] = true;

            $blockedAt = $this->randDate('-6 months');
            $isTemp    = mt_rand(0,1);
            $chunk[] = [
                'ip_address'    => $ip,
                'reason'        => $this->ipBlockReasons[mt_rand(0, $rCount - 1)],
                'blocked_by'    => mt_rand(0,9) < 7 ? 'auto' : 'admin',
                'block_count'   => mt_rand(1, 15),
                'blocked_until' => $isTemp ? date('Y-m-d H:i:s', strtotime($blockedAt) + mt_rand(3600, 86400)) : null,
                'blocked_at'    => $blockedAt,
                'created_at'    => $blockedAt,
                'updated_at'    => $blockedAt,
            ];
        }
        if ($chunk) { DB::table('ip_blacklist')->insert($chunk); DB::statement('COMMIT;'); }
        $this->printDone('ip_blacklist', self::IP_BLACKLIST, $start);
    }

    // ── IP Whitelist ───────────────────────────────────────────────────────

    private function seedIpWhitelist(): void
    {
        $this->command->info('→ Seeding ip_whitelist (' . number_format(self::IP_WHITELIST) . ')...');
        $start  = microtime(true);
        $chunk  = [];
        $labels = ['Office Jakarta','Office Surabaya','Office Bandung','VPN Corporate','Dev Server','CI/CD Pipeline','Staging Server'];
        $usedIp = [];

        for ($i = 0; $i < self::IP_WHITELIST; $i++) {
            do { $ip = $this->randIp(); } while (isset($usedIp[$ip]));
            $usedIp[$ip] = true;
            $createdAt = $this->randDate('-1 year');
            $chunk[] = [
                'ip_address' => $ip,
                'label'      => $labels[array_rand($labels)] . ' #' . ($i + 1),
                'added_by'   => 'admin',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }
        if ($chunk) { DB::table('ip_whitelist')->insert($chunk); DB::statement('COMMIT;'); }
        $this->printDone('ip_whitelist', self::IP_WHITELIST, $start);
    }

    // ── User Blocks ────────────────────────────────────────────────────────

    private function seedUserBlocks(): void
    {
        $this->command->info('→ Seeding user_blocks (' . number_format(self::USER_BLOCKS) . ')...');
        $start     = microtime(true);
        $chunk     = [];
        $userCount = count($this->userIds);
        $rCount    = count($this->blockReasons);

        for ($i = 0; $i < self::USER_BLOCKS; $i++) {
            $createdAt   = $this->randDate('-6 months');
            $isUnblocked = mt_rand(0,9) < 6;
            $isTemp      = mt_rand(0,1);
            $createdTs   = strtotime($createdAt);

            $chunk[] = [
                'user_id'       => $this->userIds[mt_rand(0, $userCount - 1)],
                'reason'        => $this->blockReasons[mt_rand(0, $rCount - 1)],
                'blocked_by'    => mt_rand(0,9) < 7 ? 'auto' : 'admin',
                'block_count'   => mt_rand(1, 5),
                'blocked_until' => $isTemp ? date('Y-m-d H:i:s', $createdTs + mt_rand(3600, 86400)) : null,
                'unblocked_at'  => $isUnblocked ? date('Y-m-d H:i:s', $createdTs + mt_rand(3600, 172800)) : null,
                'unblocked_by'  => $isUnblocked ? 'admin' : null,
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];

            if (count($chunk) >= self::CHUNK) {
                DB::table('user_blocks')->insert($chunk);
                $chunk = [];
                DB::statement('COMMIT;');
            }
        }
        if ($chunk) { DB::table('user_blocks')->insert($chunk); DB::statement('COMMIT;'); }
        $this->printDone('user_blocks', self::USER_BLOCKS, $start);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function buildPools(): void
    {
        $this->command->info('→ Building data pools...');

        $this->ips = [];
        for ($i = 0; $i < 500; $i++) {
            $this->ips[] = $this->randIp();
        }

        $this->fingerprints = [];
        for ($i = 0; $i < 2_000; $i++) {
            $this->fingerprints[] = bin2hex(random_bytes(32));
        }
    }

    private function randIp(): string
    {
        return mt_rand(1,254).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(1,254);
    }

    private function randEmail(): string
    {
        $first = $this->firstNames[array_rand($this->firstNames)];
        $last  = $this->lastNames[array_rand($this->lastNames)];
        return strtolower($first.'.'.$last.mt_rand(1,9999)).'@'.$this->domains[array_rand($this->domains)];
    }

    private function randDate(string $from, string $to = 'now'): string
    {
        return date('Y-m-d H:i:s', mt_rand(strtotime($from), strtotime($to)));
    }

    private function printDone(string $table, int $count, float $start): void
    {
        $elapsed = round(microtime(true) - $start, 1);
        $this->command->info("  ✓ {$table}: " . number_format($count) . " rows — {$elapsed}s");
    }
}