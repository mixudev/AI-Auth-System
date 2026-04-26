@extends('layouts.app-dashboard')

@section('title', 'Integrated Applications')
@section('page-title', 'Application Ecosystem')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header & Summary -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Application Ecosystem</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Monitoring status integrasi dan aktivitas user di seluruh jaringan SSO.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md shadow-sm">
                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Total Apps:</span>
                <span class="text-sm font-bold text-slate-800 dark:text-white ml-1">{{ $clients->count() }}</span>
            </div>
            <div class="px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md shadow-sm">
                <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Active Users:</span>
                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 ml-1">{{ $clients->sum('active_users_count') }}</span>
            </div>
        </div>
    </div>

    <!-- Apps List (Minimalist Table/Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($clients as $client)
            @php
                $domain = 'Localhost';
                $clientUrl = '#';
                if ($client->webhook_url) {
                    $parsed = parse_url($client->webhook_url);
                    $domain = $parsed['host'] ?? 'Localhost';
                    $clientUrl = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '') . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
                }
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md overflow-hidden flex flex-col transition-all hover:border-indigo-500/50 hover:shadow-md group">
                <!-- Top Header -->
                <div class="p-4 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-base font-bold border border-indigo-100 dark:border-indigo-500/20">
                            {{ strtoupper(substr($client->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white line-clamp-1">{{ $client->name }}</h3>
                            <div class="text-[10px] font-mono text-slate-400 tracking-tight">{{ $domain }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ $clientUrl }}" target="_blank" class="w-7 h-7 flex items-center justify-center rounded bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-indigo-500 transition-colors" title="Buka Situs">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Active Users Preview -->
                <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">User Aktif</span>
                            <span class="text-[11px] font-mono font-bold text-emerald-500">{{ $client->active_users_count }} Sesi</span>
                        </div>
                        <div class="flex items-center -space-x-2">
                            @forelse($client->active_users_preview as $user)
                                <img class="h-8 w-8 rounded-full ring-2 ring-white dark:ring-slate-900 object-cover" 
                                     src="{{ $user->avatar_url }}" 
                                     alt="{{ $user->name }}"
                                     title="{{ $user->name }}">
                            @empty
                                <span class="text-[11px] text-slate-400 italic">Tidak ada aktivitas</span>
                            @endforelse
                            @if($client->active_users_count > 5)
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-800 ring-2 ring-white dark:ring-slate-900 text-[10px] font-bold text-slate-500">
                                    +{{ $client->active_users_count - 5 }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Connected</span>
                        </div>
                        <a href="{{ route('sso.applications.show', $client) }}" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors flex items-center gap-1 group/btn">
                            View Detail
                            <i class="fa-solid fa-chevron-right text-[9px] group-hover/btn:translate-x-0.5 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800 rounded-md text-center">
                <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800 rounded-md flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i class="fa-solid fa-cubes text-xl"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Belum ada Aplikasi</h3>
                <p class="text-xs text-slate-500 mt-1">Daftarkan client pertama Anda untuk memantau aktivitas di sini.</p>
                <a href="{{ route('sso.clients.index') }}" class="inline-flex mt-5 bg-indigo-600 text-white px-4 py-2 rounded-md font-bold text-xs hover:bg-indigo-700 transition-all shadow-sm">
                    Kelola Client Apps
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
