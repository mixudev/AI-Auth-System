@extends('layouts.app-dashboard')

@section('title', 'Audit Log Detail')
@section('page-title', 'Audit Log Detail')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('audit-logs.center', ['tab' => 'audit']) }}" class="w-8 h-8 flex items-center justify-center rounded-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-800 transition-colors shadow-sm">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Detail Aktivitas Sistem</h2>
                <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Entry Trace ID: #{{ str_pad($auditLog->id, 8, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Data Body -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-code text-indigo-500"></i> JSON Payload Data
                    </h3>
                </div>
                
                <div class="p-6 space-y-6">
                    @if($auditLog->new_values)
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">Input / New Values</span>
                            <span class="text-[9px] bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-sm border border-emerald-100 dark:border-emerald-800 font-bold">LATEST DATA</span>
                        </div>
                        <div class="relative group">
                            <pre class="p-4 bg-slate-950 rounded-sm text-[11px] font-mono text-emerald-400 overflow-x-auto border border-slate-800 leading-relaxed">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>

                    @if($auditLog->old_values)
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">Previous Values</span>
                            <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded-sm border border-slate-200 dark:border-slate-700 font-bold">OLD STATE</span>
                        </div>
                        <div class="relative">
                            <pre class="p-4 bg-slate-50 dark:bg-slate-900 rounded-sm text-[11px] font-mono text-rose-500 dark:text-rose-400/80 overflow-x-auto border border-slate-200 dark:border-slate-800 leading-relaxed">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="flex flex-col items-center justify-center py-12 text-slate-400 italic text-xs">
                        <i class="fa-solid fa-ghost text-3xl mb-3 opacity-10"></i>
                        No data payload available for this event.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Meta Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm p-6">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-5 border-b border-slate-100 dark:border-slate-800 pb-2">Technical Metadata</h3>
                
                <div class="space-y-5">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-sm bg-slate-50 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user text-xs text-slate-400"></i>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Pelaku Aksi</span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $auditLog->user->name ?? 'System Process' }}</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-sm bg-slate-50 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-bolt text-xs text-amber-500"></i>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Nama Event</span>
                            <span class="text-[10px] font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $auditLog->event }}</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-sm bg-slate-50 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-network-wired text-xs text-slate-400"></i>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">IP & Location</span>
                            <span class="text-xs font-mono text-slate-600 dark:text-slate-300">{{ $auditLog->ip_address }}</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-sm bg-slate-50 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-clock text-xs text-slate-400"></i>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-slate-400 uppercase">Waktu Eksekusi</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300">{{ $auditLog->created_at->format('d M Y, H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm p-6">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Origin Request</h3>
                <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-sm border border-slate-100 dark:border-slate-800">
                    <p class="text-[9px] font-mono text-slate-500 leading-relaxed break-all">{{ $auditLog->user_agent }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
