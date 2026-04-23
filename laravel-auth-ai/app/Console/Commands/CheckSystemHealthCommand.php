<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CheckSystemHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-system-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform overall system health checks and snapshot the result for the dashboard.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = [
            'database'  => [
                'status' => $this->checkDatabase(),
                'label'  => 'Database',
                'desc'   => 'Penyimpanan data utama sistem.'
            ],
            'cache'     => [
                'status' => $this->checkCache(),
                'label'  => 'System Cache',
                'desc'   => 'Akselerasi performa & respon.'
            ],
            'storage'   => [
                'status' => $this->checkStorage(),
                'label'  => 'Storage ',
                'desc'   => 'Penyimpanan aset & log sistem.'
            ],
            'ai_engine' => [
                'status' => $this->checkAiService(),
                'label'  => 'AI Risk Engine',
                'desc'   => 'Analisis mitigasi ancaman login.'
            ],
            'smtp'      => [
                'status' => $this->checkSmtp(),
                'label'  => 'SMTP Gateway',
                'desc'   => 'Sistem verifikasi & notifikasi.'
            ],
        ];

        // Determine overall status
        $overall = 'Operational';
        $rawStatuses = array_column($status, 'status');
        if (in_array('Down', $rawStatuses) || in_array('Critical', $rawStatuses)) {
            $overall = 'Critical';
        } elseif (in_array('Degraded', $rawStatuses)) {
            $overall = 'Degraded';
        }

        $data = [
            'status'    => $overall,
            'details'   => $status,
            'timestamp' => now()->toIso8601String(),
        ];

        // Store snapshot forever (or until next check)
        Cache::forever('system_health_snapshot', $data);

        $this->info('System health check completed. Snapshot saved.');
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'Operational';
        } catch (\Exception $e) {
            Log::error('Health Check: Database connection failed: ' . $e->getMessage());
            return 'Down';
        }
    }

    /**
     * Check cache (Redis/File) connectivity.
     */
    private function checkCache(): string
    {
        try {
            Cache::driver()->get('health_check_ping');
            return 'Operational';
        } catch (\Exception $e) {
            Log::error('Health Check: Cache connection failed: ' . $e->getMessage());
            return 'Degraded';
        }
    }

    /**
     * Check disk storage availability.
     */
    private function checkStorage(): string
    {
        try {
            $path = base_path();
            $freeSpace = @disk_free_space($path);
            $totalSpace = @disk_total_space($path);
            
            if ($freeSpace === false || $totalSpace === false) {
                return 'Operational';
            }

            $percentageFree = ($freeSpace / $totalSpace) * 100;
            if ($percentageFree < 5) return 'Critical';
            if ($percentageFree < 15) return 'Degraded';
            
            return 'Operational';
        } catch (\Exception $e) {
            return 'Operational';
        }
    }

    /**
     * Check AI Risk Service (FastAPI) connectivity.
     */
    private function checkAiService(): string
    {
        try {
            $baseUrl = config('security.ai_service.base_url');
            $response = Http::timeout(2)->connectTimeout(1)->get($baseUrl);
            return ($response->successful() || $response->status() === 404) ? 'Operational' : 'Degraded';
        } catch (\Exception $e) {
            return 'Down';
        }
    }

    /**
     * Check SMTP server connectivity.
     */
    private function checkSmtp(): string
    {
        try {
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            
            if (!$host) return 'Operational';

            $connection = @fsockopen($host, $port, $errno, $errstr, 2);
            if ($connection) {
                fclose($connection);
                return 'Operational';
            }
            return 'Down';
        } catch (\Exception $e) {
            return 'Down';
        }
    }
}
