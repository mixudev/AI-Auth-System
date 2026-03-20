@extends('layouts.app-dashboard')

@section('title', 'Security Dashboard')
@section('page-title', 'Dashboard')
@section('page-sub', 'Monitoring keamanan & aktivitas login secara realtime')

@section('content')

{{-- ============================================================
     KOMPATIBILITAS DENGAN LAYOUT:
     - Tidak ada Alpine.js (layout tidak load Alpine)
     - Tidak ada duplicate header / notif / dark toggle
     - isDark() → cek classList('dark') pada <html>, sama persis dengan layout
     - rebuildCharts() → di-expose ke window → dipanggil toggleDark() di layout
     - Chart.js TIDAK di-load ulang (sudah ada di <head> layout)
     - Dark mode key: localStorage('theme'), bukan 'darkMode'
     - Period selector: plain JS (window.setPeriod), redirect dengan ?period=
     ============================================================ --}}

{{-- ── TOOLBAR ── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Security Dashboard </h1>
        <p class="text-xs text-slate-400 mt-0.5">Monitoring keamanan &amp; aktivitas login secara realtime</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
            @foreach(['24h' => '24 Jam', '7d' => '7 Hari', '30d' => '30 Hari'] as $val => $label)
            <button
                data-period="{{ $val }}"
                onclick="setPeriod('{{ $val }}')"
                class="period-btn px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 {{ $val === ($currentPeriod ?? '7d') ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
            >{{ $label }}</button>
            @endforeach
        </div>
        <button
            onclick="refreshDashboard()"
            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors"
        >
            <svg id="refreshIcon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
    </div>
</div>

{{-- ── ALERT BANNER ── --}}
@if(isset($criticalAlerts) && $criticalAlerts->count() > 0)
<div class="flex items-start gap-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl p-4 mb-6">
    <div class="flex-shrink-0 w-8 h-8 bg-red-100 dark:bg-red-500/20 rounded-lg flex items-center justify-center">
        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-red-800 dark:text-red-300">{{ $criticalAlerts->count() }} Notifikasi Keamanan Aktif</p>
        <p class="text-xs text-red-600 dark:text-red-400 mt-0.5 truncate">{{ $criticalAlerts->first()->message ?? 'Terdapat ancaman yang membutuhkan perhatian segera.' }}</p>
    </div>
    <a href="{{ route('security.notifications') }}" class="flex-shrink-0 text-xs font-medium text-red-700 dark:text-red-400 hover:underline">Lihat semua →</a>
</div>
@endif

{{-- ── STAT CARDS ── --}}
@php
$cards = [
    [
        'label'     => 'Total Login',
        'value'     => $stats['total_logins'] ?? 0,
        'sub'       => 'percobaan masuk',
        'trend'     => $stats['login_trend'] ?? null,
        'color'     => 'indigo',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>'
    ],
    [
        'label'     => 'Login Sukses',
        'value'     => $stats['success_logins'] ?? 0,
        'sub'       => 'berhasil masuk',
        'trend'     => null, 'color' => 'emerald',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
     ],
    [
        'label'     => 'IP Diblokir',
        'value'     => $stats['blocked_ips'] ?? 0,
        'sub'       => 'blacklisted aktif',
        'trend'     => null, 'color' => 'red',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>'
     ],
    [
        'label'     => 'User Diblokir',
        'value'     => $stats['blocked_users'] ?? 0,
        'sub'       => 'akun terkunci',
        'trend'     => null, 'color' => 'orange',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>'
     ],
    [
        'label'     => 'OTP Aktif',
        'value'     => $stats['active_otps'] ?? 0,
        'sub'       => 'menunggu verifikasi',
        'trend'     => null, 'color' => 'sky',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>'
     ],
    [
        'label'     => 'Failed Jobs',
        'value'     => $stats['failed_jobs'] ?? 0,
        'sub'       => 'queue gagal',
        'trend'     => null,
        'color'     => ($stats['failed_jobs'] ?? 0) > 0 ? 'red' : 'slate',
        'icon'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
     ],
];
$colorMap = [
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-500/10',   'ic' => 'text-indigo-600 dark:text-indigo-400',   'ring' => 'ring-indigo-100 dark:ring-indigo-500/20'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'ic' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
    'red'     => ['bg' => 'bg-red-50 dark:bg-red-500/10',         'ic' => 'text-red-600 dark:text-red-400',         'ring' => 'ring-red-100 dark:ring-red-500/20'],
    'orange'  => ['bg' => 'bg-orange-50 dark:bg-orange-500/10',   'ic' => 'text-orange-600 dark:text-orange-400',   'ring' => 'ring-orange-100 dark:ring-orange-500/20'],
    'sky'     => ['bg' => 'bg-sky-50 dark:bg-sky-500/10',         'ic' => 'text-sky-600 dark:text-sky-400',         'ring' => 'ring-sky-100 dark:ring-sky-500/20'],
    'slate'   => ['bg' => 'bg-slate-100 dark:bg-slate-800',       'ic' => 'text-slate-500 dark:text-slate-400',     'ring' => 'ring-slate-200 dark:ring-slate-700'],
];
@endphp

<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @foreach($cards as $card)
    @php $c = $colorMap[$card['color']]; @endphp
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 flex flex-col gap-3 hover:shadow-md dark:hover:shadow-black/20 transition-shadow">
        <div class="flex items-center justify-between">
            <div class="w-9 h-9 rounded-lg {{ $c['bg'] }} ring-1 {{ $c['ring'] }} flex items-center justify-center">
                <svg style="width:18px;height:18px" class="{{ $c['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $card['icon'] !!}</svg>
            </div>
            @if($card['trend'] !== null)
            <span class="text-xs font-semibold {{ $card['trend'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                {{ $card['trend'] >= 0 ? '▲' : '▼' }} {{ abs($card['trend']) }}%
            </span>
            @endif
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">{{ number_format($card['value']) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $card['label'] }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5">{{ $card['sub'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════
     ── ROW BARU: 3 Chart Aktivitas Hari Ini ──
     Data yang dibutuhkan dari controller (array per jam, 0–23):
       $todaySuccessHourly  → login sukses per jam
       $todayOtpHourly      → login OTP per jam
       $todayFailedHourly   → login gagal per jam
       $todayBlockedHourly  → login blocked per jam
     Jika belum ada di controller, cukup kirimkan array kosong [].
══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Card 1: Login Sukses Hari Ini --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="flex items-center gap-1.5 mb-0.5">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Login Sukses Hari Ini</h3>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 pl-3.5">Distribusi per jam (00–23)</p>
            </div>
            <div class="text-right">
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums leading-none">
                    {{ number_format(array_sum($todaySuccessHourly ?? [])) }}
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">total hari ini</p>
            </div>
        </div>
        <div class="relative h-28">
            <canvas id="todaySuccessChart"></canvas>
        </div>
    </div>

    {{-- Card 2: Login OTP Hari Ini --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="flex items-center gap-1.5 mb-0.5">
                    <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                    <h3 class="text-xs font-semibold text-slate-700 dark:text-slate-200">Login OTP Hari Ini</h3>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 pl-3.5">Distribusi per jam (00–23)</p>
            </div>
            <div class="text-right">
                <p class="text-xl font-bold text-amber-500 dark:text-amber-400 tabular-nums leading-none">
                    {{ number_format(array_sum($todayOtpHourly ?? [])) }}
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">total hari ini</p>
            </div>
        </div>
        <div class="relative h-28">
            <canvas id="todayOtpChart"></canvas>
        </div>
    </div>

    {{-- Card 3: Login Gagal & Block Hari Ini --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Gagal</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Block</span>
                    </div>
                </div>
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Gagal &amp; Block Hari Ini</p>
            </div>
            <div class="text-right">
                @php
                $totalThreat = array_sum($todayFailedHourly ?? []) + array_sum($todayBlockedHourly ?? []);
                @endphp
                <p class="text-xl font-bold text-red-600 dark:text-red-400 tabular-nums leading-none">
                    {{ number_format($totalThreat) }}
                </p>
                <p class="text-[10px] text-slate-400 mt-0.5">total ancaman</p>
            </div>
        </div>
        <div class="relative h-28">
            <canvas id="todayThreatChart"></canvas>
        </div>
    </div>

</div>

{{-- ── ROW 1: Login Activity + Keputusan AI ── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6 items-stretch">

    <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5 flex flex-col">
        <div class="flex items-center justify-between mb-5 flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Aktivitas Login</h2>
                <p class="text-xs text-slate-400 mt-0.5">Tren harian berdasarkan status percobaan</p>
            </div>
            <div class="hidden sm:flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-emerald-500 rounded-full inline-block"></span>Sukses</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-amber-500 rounded-full inline-block"></span>OTP</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-red-500 rounded-full inline-block"></span>Blocked</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-slate-400 rounded-full inline-block"></span>Gagal</span>
            </div>
        </div>
        <div class="relative flex-1 min-h-[240px] max-h-[320px]">
            <canvas id="loginActivityChart"></canvas>
        </div>
    </div>

    {{-- Card Keputusan AI: horizontal layout, donut kiri + legend kanan --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5 flex flex-col">

        {{-- Header --}}
        <div class="mb-4 flex-shrink-0">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Keputusan AI</h2>
            <p class="text-xs text-slate-400 mt-0.5">Distribusi hasil analisis risiko</p>
        </div>

        @php
        $decisions = [
            ['label' => 'ALLOW',    'key' => 'ALLOW',    'dot' => 'bg-emerald-500', 'txt' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'OTP',      'key' => 'OTP',      'dot' => 'bg-amber-400',   'txt' => 'text-amber-600 dark:text-amber-400'],
            ['label' => 'BLOCK',    'key' => 'BLOCK',    'dot' => 'bg-red-500',      'txt' => 'text-red-600 dark:text-red-400'],
            ['label' => 'FALLBACK', 'key' => 'FALLBACK', 'dot' => 'bg-slate-400',    'txt' => 'text-slate-500 dark:text-slate-400'],
            ['label' => 'PENDING',  'key' => 'PENDING',  'dot' => 'bg-sky-400',      'txt' => 'text-sky-600 dark:text-sky-400'],
        ];
        $decTotal = 0;
        foreach ($decisions as $_d) { $decTotal += $decisionBreakdown[$_d['key']] ?? 0; }

        // Hitung insight otomatis
        $allowVal   = $decisionBreakdown['ALLOW']  ?? 0;
        $blockVal   = $decisionBreakdown['BLOCK']  ?? 0;
        $otpVal     = $decisionBreakdown['OTP']    ?? 0;
        $allowPct   = $decTotal > 0 ? round(($allowVal / $decTotal) * 100, 1) : 0;
        $blockPct   = $decTotal > 0 ? round(($blockVal / $decTotal) * 100, 1) : 0;
        $otpPct     = $decTotal > 0 ? round(($otpVal   / $decTotal) * 100, 1) : 0;

        // Level ancaman berdasarkan % BLOCK
        if ($blockPct >= 20) {
            $threatLevel = ['label' => 'TINGGI',  'bg' => 'bg-red-100 dark:bg-red-500/15',     'txt' => 'text-red-600 dark:text-red-400',     'icon' => '⚠'];
        } elseif ($blockPct >= 8) {
            $threatLevel = ['label' => 'SEDANG',  'bg' => 'bg-amber-100 dark:bg-amber-500/15', 'txt' => 'text-amber-600 dark:text-amber-400', 'icon' => '●'];
        } else {
            $threatLevel = ['label' => 'RENDAH',  'bg' => 'bg-emerald-100 dark:bg-emerald-500/15', 'txt' => 'text-emerald-600 dark:text-emerald-400', 'icon' => '✓'];
        }
        @endphp

        {{-- Body: donut kiri + legend kanan --}}
        <div class="flex items-center gap-4 flex-shrink-0">

            {{-- Donut kecil, fixed --}}
            <div class="flex-shrink-0 relative" style="width:96px;height:96px;">
                <canvas id="decisionDonut" style="width:96px;height:96px;"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-xs font-bold text-slate-800 dark:text-white tabular-nums leading-none">{{ number_format($decTotal) }}</span>
                    <span class="text-[8px] text-slate-400 mt-0.5">total</span>
                </div>
            </div>

            {{-- Legend kanan: 2 kolom grid, super compact --}}
            <div class="flex-1 min-w-0">
                <div class="grid grid-cols-2 gap-x-2 gap-y-2">
                    @foreach($decisions as $d)
                    @php
                    $val = $decisionBreakdown[$d['key']] ?? 0;
                    $pct = $decTotal > 0 ? round(($val / $decTotal) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center gap-1.5 min-w-0">
                        <div class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $d['dot'] }}"></div>
                        <div class="min-w-0">
                            <div class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-wide leading-none">{{ $d['label'] }}</div>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-xs font-bold tabular-nums {{ $d['txt'] }}">{{ number_format($val) }}</span>
                                <span class="text-[9px] text-slate-400 tabular-nums">{{ $pct }}%</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[9px] text-slate-400 uppercase tracking-wide">Total</span>
                    <span class="text-xs font-bold tabular-nums text-slate-700 dark:text-slate-200">{{ number_format($decTotal) }}</span>
                </div>
            </div>

        </div>

        {{-- Divider --}}
        <div class="my-4 border-t border-slate-100 dark:border-slate-800 flex-shrink-0"></div>

        {{-- Insight cards — mengisi sisa ruang secara natural --}}
        <div class="flex-1 flex flex-col justify-between gap-3">

            {{-- Baris 1: Tingkat keberhasilan + Level ancaman --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg px-3 py-2.5">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wide mb-1">Keberhasilan</p>
                    <p class="text-lg font-bold tabular-nums text-emerald-600 dark:text-emerald-400 leading-none">{{ $allowPct }}%</p>
                    <p class="text-[9px] text-slate-400 mt-0.5">login diizinkan</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg px-3 py-2.5">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wide mb-1">Level Ancaman</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold {{ $threatLevel['bg'] }} {{ $threatLevel['txt'] }}">
                            {{ $threatLevel['icon'] }} {{ $threatLevel['label'] }}
                        </span>
                    </div>
                    <p class="text-[9px] text-slate-400 mt-1">{{ $blockPct }}% diblokir</p>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- ── ROW 2: Risk Score + Top Threat IPs ── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6 items-stretch">

    <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5 flex flex-col">
        <div class="flex items-center justify-between mb-5 flex-shrink-0">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Tren Risk Score</h2>
                <p class="text-xs text-slate-400 mt-0.5">Rata-rata &amp; puncak skor risiko harian</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-violet-500 rounded-full inline-block"></span>Avg</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-red-400 rounded-full inline-block"></span>Max</span>
            </div>
        </div>
        <div class="relative flex-1 min-h-[160px]">
            <canvas id="riskScoreChart"></canvas>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Top Threat IPs</h2>
                <p class="text-xs text-slate-400 mt-0.5">IP dengan aktivitas blocked terbanyak</p>
            </div>
            <a href="{{ route('security.blacklist') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua</a>
        </div>
        <div class="space-y-2">
            @forelse($topThreatIps ?? [] as $i => $ip)
            @if($i >= 6) @break @endif
            <div class="flex items-center justify-between py-1.5 border-b border-slate-50 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></div>
                    <span class="text-xs font-mono text-slate-700 dark:text-slate-300 truncate">{{ $ip->ip_address }}</span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($ip->max_risk >= 80)
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400 rounded">HIGH</span>
                    @elseif($ip->max_risk >= 50)
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 rounded">MED</span>
                    @else
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded">LOW</span>
                    @endif
                    <span class="text-xs font-semibold tabular-nums text-slate-600 dark:text-slate-300 w-8 text-right">{{ number_format($ip->attempts) }}×</span>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <svg class="w-8 h-8 text-slate-200 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-xs text-slate-400 dark:text-slate-600">Tidak ada ancaman terdeteksi</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── ROW 3: Recent Logs + Mini Cards ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">

    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Log Login Terbaru</h2>
                <p class="text-xs text-slate-400 mt-0.5">10 percobaan login terakhir</p>
            </div>
            <a href="{{ route('security.logs') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-5 py-2.5 font-medium text-slate-400 dark:text-slate-600">Email / User</th>
                        <th class="text-left px-3 py-2.5 font-medium text-slate-400 dark:text-slate-600 hidden sm:table-cell">IP</th>
                        <th class="text-left px-3 py-2.5 font-medium text-slate-400 dark:text-slate-600 hidden md:table-cell">Risk</th>
                        <th class="text-left px-3 py-2.5 font-medium text-slate-400 dark:text-slate-600">Status</th>
                        <th class="text-right px-5 py-2.5 font-medium text-slate-400 dark:text-slate-600 hidden lg:table-cell">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs ?? [] as $log)
                    <tr class="border-b border-slate-50 dark:border-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-2.5">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0">
                                    {{ strtoupper(substr($log->email_attempted, 0, 1)) }}
                                </div>
                                <span class="font-mono text-slate-600 dark:text-slate-300 truncate max-w-[140px]">{{ $log->email_attempted }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 hidden sm:table-cell">
                            <span class="font-mono text-slate-500 dark:text-slate-400">{{ $log->ip_address }}</span>
                            @if($log->country_code)<span class="ml-1 text-slate-400 dark:text-slate-600">{{ $log->country_code }}</span>@endif
                        </td>
                        <td class="px-3 py-2.5 hidden md:table-cell">
                            @if($log->risk_score !== null)
                            <span class="tabular-nums font-medium {{ $log->risk_score >= 80 ? 'text-red-600 dark:text-red-400' : ($log->risk_score >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">{{ $log->risk_score }}</span>
                            @else<span class="text-slate-300 dark:text-slate-700">—</span>@endif
                        </td>
                        <td class="px-3 py-2.5">
                            @php
                            $sm = ['success'=>'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400','otp_required'=>'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400','blocked'=>'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400','failed'=>'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400','fallback'=>'bg-sky-100 dark:bg-sky-500/15 text-sky-700 dark:text-sky-400'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $sm[$log->status] ?? $sm['failed'] }}">
                                {{ strtoupper(str_replace('_', ' ', $log->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-right hidden lg:table-cell">
                            <span class="text-slate-400 dark:text-slate-600 tabular-nums">{{ $log->occurred_at?->diffForHumans() }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 dark:text-slate-600 text-xs">Belum ada data log</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Status OTP</h2>
            <div class="space-y-2.5">
                @foreach([['label'=>'Aktif & Belum Verified','val'=>$otpSummary['active']??0,'dot'=>'bg-amber-400'],['label'=>'Sudah Diverifikasi','val'=>$otpSummary['verified']??0,'dot'=>'bg-emerald-500'],['label'=>'Kedaluwarsa','val'=>$otpSummary['expired']??0,'dot'=>'bg-slate-300 dark:bg-slate-600']] as $o)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $o['dot'] }}"></div>
                        <span class="text-xs text-slate-600 dark:text-slate-300">{{ $o['label'] }}</span>
                    </div>
                    <span class="text-xs font-semibold tabular-nums text-slate-800 dark:text-white">{{ number_format($o['val']) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Perangkat Terpercaya</h2>
            <div class="grid grid-cols-3 gap-3 text-center">
                @foreach([['label'=>'Aktif','val'=>$deviceSummary['total']??0,'color'=>'text-emerald-600 dark:text-emerald-400'],['label'=>'Expired','val'=>$deviceSummary['expired']??0,'color'=>'text-amber-600 dark:text-amber-400'],['label'=>'Revoked','val'=>$deviceSummary['revoked']??0,'color'=>'text-red-600 dark:text-red-400']] as $d)
                <div>
                    <p class="text-xl font-bold tabular-nums {{ $d['color'] }}">{{ number_format($d['val']) }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5">{{ $d['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notifikasi</h2>
                <a href="{{ route('security.notifications') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Semua</a>
            </div>
            <div class="space-y-2">
                @forelse($recentNotifs ?? [] as $notif)
                @php
                $nm=['error'=>['bg'=>'bg-red-100 dark:bg-red-500/15','t'=>'text-red-500'],'warning'=>['bg'=>'bg-amber-100 dark:bg-amber-500/15','t'=>'text-amber-500'],'success'=>['bg'=>'bg-emerald-100 dark:bg-emerald-500/15','t'=>'text-emerald-500'],'info'=>['bg'=>'bg-sky-100 dark:bg-sky-500/15','t'=>'text-sky-500']];
                $ni=$nm[$notif->type]??['bg'=>'bg-slate-100 dark:bg-slate-800','t'=>'text-slate-400'];
                @endphp
                <div class="flex items-start gap-2.5">
                    <div class="w-6 h-6 flex-shrink-0 rounded {{ $ni['bg'] }} flex items-center justify-center mt-0.5">
                        <svg class="w-3 h-3 {{ $ni['t'] }}" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $notif->title }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-600 truncate">{{ $notif->created_at?->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 dark:text-slate-600 text-center py-3">Tidak ada notifikasi baru</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     SCRIPT — IIFE agar tidak polusi global scope
     Chart.js sudah ada di layout, tidak di-load ulang.
     window.rebuildCharts → dipanggil toggleDark() di layout.
     window.refreshDashboard → dipanggil tombol Refresh.
     window.setPeriod → dipanggil period pill.
     ============================================================ --}}
<script>
(function () {

    // ── isDark: samakan dengan cara layout ──────────────────────────────────
    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    // ── Palet warna chart ────────────────────────────────────────────────────
    var C = {
        emerald : { s: '#10b981', a: function(v){ return 'rgba(16,185,129,'+v+')';  } },
        amber   : { s: '#f59e0b', a: function(v){ return 'rgba(245,158,11,'+v+')';  } },
        red     : { s: '#ef4444', a: function(v){ return 'rgba(239,68,68,'+v+')';   } },
        slate   : { s: '#94a3b8', a: function(v){ return 'rgba(148,163,184,'+v+')'; } },
        sky     : { s: '#38bdf8', a: function(v){ return 'rgba(56,189,248,'+v+')';  } },
        violet  : { s: '#8b5cf6', a: function(v){ return 'rgba(139,92,246,'+v+')';  } },
    };

    function gridColor()    { return isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';       }
    function tickColor()    { return isDark() ? '#475569' : '#94a3b8';                                 }
    function tooltipBg()    { return isDark() ? '#1e293b' : '#ffffff';                                 }
    function tooltipFg()    { return isDark() ? '#e2e8f0' : '#1e293b';                                 }
    function borderColor()  { return isDark() ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';        }

    // ── Data dari server ─────────────────────────────────────────────────────
    var chartLabels    = @json($chartLabels ?? []);
    var chartSuccess   = @json($chartSuccess ?? []);
    var chartOtp       = @json($chartOtp ?? []);
    var chartBlocked   = @json($chartBlocked ?? []);
    var chartFailed    = @json($chartFailed ?? []);
    var riskAvg        = @json($riskAvg ?? []);
    var riskMax        = @json($riskMax ?? []);
    const decisionCounts = @json($decisionCounts);

    // ── Data Hari Ini (per jam) ───────────────────────────────────────────────
    // Label jam: 00, 01, ..., 23
    var hourLabels = Array.from({length: 24}, function(_, i){ return (i < 10 ? '0' : '') + i; });

    var todaySuccessHourly = @json($todaySuccessHourly ?? array_fill(0, 24, 0));
    var todayOtpHourly     = @json($todayOtpHourly     ?? array_fill(0, 24, 0));
    var todayFailedHourly  = @json($todayFailedHourly  ?? array_fill(0, 24, 0));
    var todayBlockedHourly = @json($todayBlockedHourly ?? array_fill(0, 24, 0));

    // ── Registry chart instances ─────────────────────────────────────────────
    var charts = {};

    // Font ikut DM Mono yang sudah load di layout
    Chart.defaults.font.family = "'DM Mono', 'ui-monospace', monospace";
    Chart.defaults.font.size   = 10;

    function baseOpts() {
        return {
            responsive          : true,
            maintainAspectRatio : false,
            interaction         : { mode: 'index', intersect: false },
            plugins: {
                legend : { display: false },
                tooltip: {
                    backgroundColor : tooltipBg(),
                    titleColor      : tooltipFg(),
                    bodyColor       : tooltipFg(),
                    borderColor     : borderColor(),
                    borderWidth     : 1,
                    padding         : 10,
                    cornerRadius    : 8,
                },
            },
            scales: {
                x: {
                    grid : { color: gridColor(), drawBorder: false },
                    ticks: { color: tickColor(), maxRotation: 0, maxTicksLimit: 8 },
                },
                y: {
                    grid        : { color: gridColor(), drawBorder: false },
                    ticks       : { color: tickColor() },
                    beginAtZero : true,
                },
            },
        };
    }

    // ── Opsi minimal untuk chart hari ini (compact, tanpa axis) ─────────────
    function miniBarOpts(tooltipLabel) {
        return {
            responsive          : true,
            maintainAspectRatio : false,
            interaction         : { mode: 'index', intersect: false },
            plugins: {
                legend : { display: false },
                tooltip: {
                    backgroundColor : tooltipBg(),
                    titleColor      : tooltipFg(),
                    bodyColor       : tooltipFg(),
                    borderColor     : borderColor(),
                    borderWidth     : 1,
                    padding         : 8,
                    cornerRadius    : 6,
                    callbacks       : {
                        title : function(ctx){ return 'Jam ' + ctx[0].label + ':00'; },
                        label : function(c){ return ' ' + (tooltipLabel || c.dataset.label) + ': ' + c.parsed.y; },
                    },
                },
            },
            scales: {
                x: {
                    grid  : { display: false },
                    border: { display: false },
                    ticks : {
                        color        : tickColor(),
                        maxRotation  : 0,
                        maxTicksLimit: 6,
                        font         : { size: 9 },
                    },
                },
                y: {
                    display    : false,
                    beginAtZero: true,
                },
            },
        };
    }

    function kill(id) { if (charts[id]) { charts[id].destroy(); delete charts[id]; } }

    // ── Build: Login Activity ────────────────────────────────────────────────
    function buildLogin() {
        kill('login');
        var el = document.getElementById('loginActivityChart');
        if (!el) return;
        function ds(lbl, data, col, fill) {
            return { label: lbl, data: data, borderColor: col.s, backgroundColor: fill ? col.a(0.08) : 'transparent', borderWidth: 2, pointRadius: 2.5, pointHoverRadius: 5, pointBackgroundColor: col.s, tension: 0.4, fill: !!fill };
        }
        var opts = baseOpts();
        opts.plugins.tooltip.callbacks = { label: function(c){ return ' '+c.dataset.label+': '+c.parsed.y.toLocaleString(); } };
        charts['login'] = new Chart(el, {
            type: 'line',
            data: { labels: chartLabels, datasets: [ds('Sukses',chartSuccess,C.emerald,true),ds('OTP',chartOtp,C.amber,false),ds('Blocked',chartBlocked,C.red,false),ds('Gagal',chartFailed,C.slate,false)] },
            options: opts,
        });
    }

    // ── Build: Risk Score ────────────────────────────────────────────────────
    function buildRisk() {
        kill('risk');
        var el = document.getElementById('riskScoreChart');
        if (!el) return;
        var opts = baseOpts();
        opts.scales.y.max = 100;
        opts.scales.y.ticks.callback = function(v){ return v+'%'; };
        charts['risk'] = new Chart(el, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [
                    { label:'Avg Risk', data:riskAvg,  borderColor:C.violet.s, backgroundColor:C.violet.a(0.1), borderWidth:2,   pointRadius:2, pointHoverRadius:4, tension:0.4, fill:true  },
                    { label:'Max Risk', data:riskMax,  borderColor:C.red.s,    backgroundColor:'transparent',   borderWidth:1.5, pointRadius:2, pointHoverRadius:4, tension:0.4, fill:false, borderDash:[4,3] },
                ],
            },
            options: opts,
        });
    }

    // ── Build: Donut ─────────────────────────────────────────────────────────
    function buildDonut() {
        kill('donut');
        var el = document.getElementById('decisionDonut');
        if (!el) return;
        charts['donut'] = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['ALLOW','OTP','BLOCK','FALLBACK','PENDING'],
                datasets: [{ data:[decisionCounts.ALLOW||0,decisionCounts.OTP||0,decisionCounts.BLOCK||0,decisionCounts.FALLBACK||0,decisionCounts.PENDING||0], backgroundColor:[C.emerald.s,C.amber.s,C.red.s,C.slate.s,C.sky.s], borderWidth:0, hoverOffset:4 }],
            },
            options: {
                responsive:true, maintainAspectRatio:false, cutout:'70%',
                plugins: {
                    legend:{display:false},
                    tooltip:{ backgroundColor:tooltipBg(), titleColor:tooltipFg(), bodyColor:tooltipFg(), borderColor:borderColor(), borderWidth:1, padding:8, cornerRadius:6, callbacks:{ label:function(c){ return ' '+c.label+': '+c.parsed.toLocaleString(); } } },
                },
            },
        });
    }

    // ── Build: Today Success ─────────────────────────────────────────────────
    function buildTodaySuccess() {
        kill('todaySuccess');
        var el = document.getElementById('todaySuccessChart');
        if (!el) return;
        var opts = miniBarOpts('Login Sukses');
        charts['todaySuccess'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label          : 'Login Sukses',
                    data           : todaySuccessHourly,
                    backgroundColor: function(ctx) {
                        var val = ctx.parsed ? ctx.parsed.y : 0;
                        var max = Math.max.apply(null, todaySuccessHourly) || 1;
                        var alpha = 0.25 + (val / max) * 0.75;
                        return 'rgba(16,185,129,' + alpha + ')';
                    },
                    borderColor    : C.emerald.s,
                    borderWidth    : 0,
                    borderRadius   : 3,
                    borderSkipped  : false,
                }],
            },
            options: opts,
        });
    }

    // ── Build: Today OTP ─────────────────────────────────────────────────────
    function buildTodayOtp() {
        kill('todayOtp');
        var el = document.getElementById('todayOtpChart');
        if (!el) return;
        var opts = miniBarOpts('Login OTP');
        charts['todayOtp'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label          : 'Login OTP',
                    data           : todayOtpHourly,
                    backgroundColor: function(ctx) {
                        var val = ctx.parsed ? ctx.parsed.y : 0;
                        var max = Math.max.apply(null, todayOtpHourly) || 1;
                        var alpha = 0.25 + (val / max) * 0.75;
                        return 'rgba(245,158,11,' + alpha + ')';
                    },
                    borderColor    : C.amber.s,
                    borderWidth    : 0,
                    borderRadius   : 3,
                    borderSkipped  : false,
                }],
            },
            options: opts,
        });
    }

    // ── Build: Today Threat (Gagal + Block) ──────────────────────────────────
    function buildTodayThreat() {
        kill('todayThreat');
        var el = document.getElementById('todayThreatChart');
        if (!el) return;
        var opts = miniBarOpts(null);
        // Stacked bar: Gagal (slate) di bawah, Blocked (red) di atas
        opts.scales.x.stacked = true;
        opts.scales.y.stacked = true;
        opts.plugins.tooltip.callbacks = {
            title : function(ctx){ return 'Jam ' + ctx[0].label + ':00'; },
            label : function(c){ return ' ' + c.dataset.label + ': ' + c.parsed.y; },
        };
        charts['todayThreat'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [
                    {
                        label          : 'Gagal',
                        data           : todayFailedHourly,
                        backgroundColor: C.slate.a(0.55),
                        borderColor    : 'transparent',
                        borderWidth    : 0,
                        borderRadius   : { topLeft: 0, topRight: 0, bottomLeft: 3, bottomRight: 3 },
                        borderSkipped  : false,
                        stack          : 'threat',
                    },
                    {
                        label          : 'Blocked',
                        data           : todayBlockedHourly,
                        backgroundColor: C.red.a(0.65),
                        borderColor    : 'transparent',
                        borderWidth    : 0,
                        borderRadius   : { topLeft: 3, topRight: 3, bottomLeft: 0, bottomRight: 0 },
                        borderSkipped  : false,
                        stack          : 'threat',
                    },
                ],
            },
            options: opts,
        });
    }

    function buildAll() {
        buildLogin();
        buildRisk();
        buildDonut();
        buildTodaySuccess();
        buildTodayOtp();
        buildTodayThreat();
    }

    // ── GLOBAL: rebuildCharts dipanggil toggleDark() di layout ───────────────
    window.rebuildCharts = function () { buildAll(); };

    // ── GLOBAL: refreshDashboard ─────────────────────────────────────────────
    window.refreshDashboard = function () {
        var icon = document.getElementById('refreshIcon');
        if (icon) {
            icon.style.transition = 'transform 0.4s ease';
            icon.style.transform  = 'rotate(360deg)';
            setTimeout(function(){ icon.style.transform = ''; }, 400);
        }
        setTimeout(function(){ window.location.reload(); }, 350);
    };

    // ── GLOBAL: setPeriod — plain JS, ganti URL lalu reload ──────────────────
    window.setPeriod = function (period) {
        document.querySelectorAll('.period-btn').forEach(function (btn) {
            var active = btn.dataset.period === period;
            btn.classList.remove('bg-white','shadow-sm','text-slate-800','text-slate-500');
            if (active) {
                btn.classList.add('bg-white', 'shadow-sm', 'text-slate-800');
            } else {
                btn.classList.add('text-slate-500');
            }
        });
        var url = new URL(window.location.href);
        url.searchParams.set('period', period);
        window.location.href = url.toString();
    };

    // ── Init setelah DOM siap ─────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildAll);
    } else {
        buildAll();
    }

}());
</script>

@endsection