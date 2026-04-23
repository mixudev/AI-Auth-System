@extends('layouts.app-dashboard')

@section('title', 'Detail Gateway: ' . $config->name)
@section('page-title', 'WhatsApp Gateway')

@section('content')
<div class="space-y-6">
    {{-- ─────────────────────────────────────────────────────────────────────────
         HEADER & QUICK ACTIONS
    ───────────────────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('wa-gateway.config.index') }}" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 hover:text-indigo-500 transition-colors shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $config->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/20">
                        {{ $config->is_active ? 'Online' : 'Offline' }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono">ID: #{{ str_pad($config->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="testConnection({{ $config->id }})" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-paper-plane text-[10px]"></i>
                Kirim Test
            </button>
            <button onclick="openEditModal({{ json_encode($config) }})" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                Edit Config
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- ─────────────────────────────────────────────────────────────────────
             LEFT: CONFIG DETAILS
        ───────────────────────────────────────────────────────────────────── --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-widest font-mono">Konfigurasi Gateway</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-2">Tujuan Penggunaan</p>
                        @php
                            $purposes = [
                                'security' => ['label' => 'Security Alert', 'icon' => 'fa-shield-halved', 'color' => 'text-red-500'],
                                'notification' => ['label' => 'System Notification', 'icon' => 'fa-bell', 'color' => 'text-sky-500'],
                                'otp' => ['label' => 'Auth / OTP', 'icon' => 'fa-key', 'color' => 'text-amber-500'],
                                'system' => ['label' => 'General System', 'icon' => 'fa-server', 'color' => 'text-slate-400'],
                            ];
                            $p = $purposes[$config->purpose] ?? $purposes['system'];
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                            <i class="fa-solid {{ $p['icon'] }} {{ $p['color'] }} text-lg"></i>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $p['label'] }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-1">Penerima Alert Utama</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200 font-mono">{{ $config->alert_phone_number }}</p>
                    </div>

                    <div>
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-1">Status Alert Otomatis</p>
                        @if($config->send_on_critical_alert)
                            <span class="flex items-center gap-2 text-emerald-500 text-xs font-bold">
                                <i class="fa-solid fa-circle-check"></i> Teraktivasi
                            </span>
                        @else
                            <span class="flex items-center gap-2 text-slate-400 text-xs font-bold">
                                <i class="fa-solid fa-circle-xmark"></i> Dinonaktifkan
                            </span>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest mb-2">Statistik Pengiriman</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/10">
                                <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-tighter">Berhasil</p>
                                <p class="text-lg font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $stats['success_count'] }}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-red-50 dark:bg-red-500/5 border border-red-100 dark:border-red-500/10">
                                <p class="text-[9px] font-bold text-red-600 uppercase tracking-tighter">Gagal</p>
                                <p class="text-lg font-bold text-red-700 dark:text-red-400 mt-1">{{ $stats['failed_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-500/20 relative overflow-hidden group">
                <div class="relative z-10">
                    <h4 class="text-sm font-bold">Butuh Bantuan API?</h4>
                    <p class="text-[11px] text-indigo-100 mt-2 leading-relaxed opacity-80">Gunakan integrasi WA Gateway ini di modul lain melalui <code>WaGatewayService</code>. Lihat dokumentasi modul untuk info lanjut.</p>
                    <button class="mt-4 px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-[10px] font-bold transition-all">Lihat Docs</button>
                </div>
                <i class="fa-brands fa-whatsapp absolute -bottom-6 -right-6 text-8xl text-white/10 group-hover:scale-110 transition-transform duration-700"></i>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────────────────────
             RIGHT: LOGS TABLE
        ───────────────────────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-widest font-mono">Riwayat Pengiriman</h3>
                    <div class="flex items-center gap-2">
                         <div class="flex items-center gap-1.5 mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] text-slate-400 font-mono">Auto-update log</span>
                        </div>
                        <button onclick="location.reload()" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 transition-colors">
                            <i class="fa-solid fa-rotate text-[10px]"></i>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                                <th class="px-6 py-4 text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest">Waktu</th>
                                <th class="px-6 py-4 text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest">Tujuan</th>
                                <th class="px-6 py-4 text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest">Pesan</th>
                                <th class="px-6 py-4 text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $log->sent_at->format('d M Y') }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $log->sent_at->format('H:i:s') }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-500 dark:text-slate-400">
                                    {{ $log->target_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="max-w-[200px] truncate text-xs text-slate-600 dark:text-slate-400" title="{{ $log->message }}">
                                        {{ $log->message }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($log->status === 'success')
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 text-[9px] font-bold border border-emerald-100 dark:border-emerald-500/20 uppercase">Terkirim</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 text-[9px] font-bold border border-red-100 dark:border-red-500/20 uppercase" title="{{ $log->error_message }}">Gagal</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                                    Belum ada data pengiriman untuk gateway ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('wa-gateway::config.partials.modals')

@push('scripts')
<script>
    const CSRF = '{{ csrf_token() }}';
    
    window.testConnection = function(id) {
        showToast('info', 'Sedang mencoba mengirim pesan test...');
        fetch(`{{ url("dashboard/wa-gateway") }}/${id}/test`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('success', res.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', res.message);
            }
        });
    };
</script>
@endpush
@endsection
