<?php

namespace App\Services\Dashboard;

use App\Models\LoginLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class DashboardChartService
{
    private function since(string $period): Carbon
    {
        return match ($period) {
            '24h'  => now()->subDay(),
            '30d'  => now()->subDays(30),
            default => now()->subDays(7),
        };
    }

    /**
     * Data untuk Login Activity Line Chart.
     *
     * Query menggunakan composite index: (occurred_at, status)
     * Data di-group per DATE(occurred_at) agar series sejajar.
     *
     * Return format:
     * [
     *   'labels'  => ['2024-01-01', '2024-01-02', ...],
     *   'success' => [120, 95, ...],
     *   'otp'     => [30, 22, ...],
     *   'blocked' => [5, 8, ...],
     *   'failed'  => [10, 7, ...],
     * ]
     */
    public function getLoginActivityChart(string $period): array
    {
        return Cache::remember("dash:chart:login:{$period}", 300, function () use ($period) {

            $since = $this->since($period);

            $rows = LoginLog::selectRaw("
                    DATE(occurred_at) as date,
                    status,
                    decision,
                    COUNT(*) as total
                ")
                ->where('occurred_at', '>=', $since)
                ->groupBy('date', 'status', 'decision')
                ->orderBy('date')
                ->get();

            // Generate tanggal lengkap
            $days   = (int) $since->diffInDays(now()) + 1;
            $labels = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $labels[] = now()->subDays($i)->toDateString();
            }

            // Index data
            $indexed = [];
            foreach ($rows as $row) {
                if ($row->status) {
                    if (!isset($indexed[$row->date]['status'][$row->status])) {
                        $indexed[$row->date]['status'][$row->status] = 0;
                    }
                    $indexed[$row->date]['status'][$row->status] += (int) $row->total;
                }
                if ($row->decision) {
                    if (!isset($indexed[$row->date]['decision'][$row->decision])) {
                        $indexed[$row->date]['decision'][$row->decision] = 0;
                    }
                    $indexed[$row->date]['decision'][$row->decision] += (int) $row->total;
                }
            }

            $success = [];
            $failed  = [];
            $blocked = [];
            $otp     = [];

            foreach ($labels as $date) {
                $success[] = $indexed[$date]['status']['success'] ?? 0;
                $failed[]  = $indexed[$date]['status']['failed'] ?? 0;
                $blocked[] = $indexed[$date]['status']['blocked'] ?? 0;
                $otp[]     = $indexed[$date]['decision']['OTP'] ?? 0;
            }

            $formattedLabels = array_map(
                fn ($d) => Carbon::parse($d)->format('d M'),
                $labels
            );

            return [
                'labels'  => $formattedLabels,
                'success' => $success,
                'failed'  => $failed,
                'blocked' => $blocked,
                'otp'     => $otp,
            ];
        });
    }

    /**
     * Data untuk Risk Score Line Chart.
     *
     * Menggunakan index: occurred_at
     * AVG dan MAX dihitung database-side (jauh lebih efisien dari PHP).
     *
     * Return format:
     * [
     *   'labels' => ['01 Jan', ...],
     *   'avg'    => [45.2, 38.7, ...],
     *   'max'    => [90, 85, ...],
     * ]
     */
    public function getRiskScoreChart(string $period): array
    {
        return Cache::remember("dash:chart:risk:{$period}", 300, function () use ($period) {
            $since = $this->since($period);

            $rows = LoginLog::selectRaw("
                DATE(occurred_at) as date,
                ROUND(AVG(risk_score), 1) as avg_risk,
                MAX(risk_score) as max_risk
            ")
            ->where('occurred_at', '>=', $since)
            ->whereNotNull('risk_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

            $days   = (int) $since->diffInDays(now()) + 1;
            $labels = [];
            $avg    = [];
            $max    = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date     = now()->subDays($i)->toDateString();
                $labels[] = Carbon::parse($date)->format('d M');
                $avg[]    = $rows[$date]->avg_risk ?? 0;
                $max[]    = $rows[$date]->max_risk ?? 0;
            }

            return compact('labels', 'avg', 'max');
        });
    }

    /**
     * Data aktivitas login hari ini, digroup per jam (0–23).
     *
     * Satu query tunggal dengan GROUP BY HOUR — efisien, pakai index occurred_at.
     * Cache 60 detik (data realtime, TTL pendek).
     *
     * Return format:
     * [
     *   'success' => [0, 0, 5, 12, ...],   // index = jam (0 = 00:00, 23 = 23:xx)
     *   'otp'     => [0, 0, 1, 3, ...],
     *   'failed'  => [0, 0, 0, 2, ...],
     *   'blocked' => [0, 0, 0, 1, ...],
     * ]
     */
    public function getTodayHourlyStats(): array
    {
        return Cache::remember('dash:chart:today_hourly', 60, function () {

            $todayStart = now()->startOfDay();
            $todayEnd   = now()->endOfDay();

            $rows = LoginLog::selectRaw("
                    HOUR(occurred_at) as hour,
                    status,
                    decision,
                    COUNT(*) as total
                ")
                ->whereBetween('occurred_at', [$todayStart, $todayEnd])
                ->groupBy('hour', 'status', 'decision')
                ->orderBy('hour')
                ->get();

            // Inisialisasi 24 slot dengan 0
            $success = array_fill(0, 24, 0);
            $otp     = array_fill(0, 24, 0);
            $failed  = array_fill(0, 24, 0);
            $blocked = array_fill(0, 24, 0);

            foreach ($rows as $row) {
                $h = (int) $row->hour;

                if ($row->status === 'success') {
                    $success[$h] += (int) $row->total;
                } elseif ($row->status === 'failed') {
                    $failed[$h] += (int) $row->total;
                } elseif ($row->status === 'blocked') {
                    $blocked[$h] += (int) $row->total;
                }

                // OTP diambil dari kolom decision (konsisten dengan getLoginActivityChart)
                if ($row->decision === 'OTP') {
                    $otp[$h] += (int) $row->total;
                }
            }

            return compact('success', 'otp', 'failed', 'blocked');
        });
    }
}