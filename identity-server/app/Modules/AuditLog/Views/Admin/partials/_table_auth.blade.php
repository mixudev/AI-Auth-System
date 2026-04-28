<div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu & IP</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identitas</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Risk & Decision</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($logs as $log)
                <tr class="transition-colors duration-200">
                    <td class="px-6 py-4">
                        <div class="text-[11px] font-bold text-slate-700 dark:text-slate-200 tabular-nums">{{ $log->occurred_at->format('d/m/Y H:i:s') }}</div>
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5 tracking-tighter">{{ $log->ip_address }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-sm bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-400 flex-shrink-0 shadow-sm">
                                @if($log->user)
                                    <i class="fa-solid fa-shield-check text-xs text-emerald-500"></i>
                                @else
                                    <i class="fa-solid fa-user-secret text-xs opacity-40"></i>
                                @endif
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate">{{ $log->email_attempted }}</span>
                                <span class="text-[9px] text-slate-400 uppercase font-black tracking-tighter">{{ $log->user ? 'Verified Account' : 'Unknown Entity' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $decisionLabels = [
                                'ALLOW' => ['label' => 'PASSED', 'color' => 'text-emerald-500'],
                                'OTP'   => ['label' => 'AI CHALLENGE', 'color' => 'text-amber-500'],
                                'BLOCK' => ['label' => 'AI BLOCKED', 'color' => 'text-rose-500'],
                                'MFA'   => ['label' => 'MFA ENFORCED', 'color' => 'text-indigo-500'],
                            ];
                            $decision = $decisionLabels[$log->decision] ?? ['label' => $log->decision ?: 'NONE', 'color' => 'text-slate-400'];
                        @endphp
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $log->risk_score >= 80 ? 'bg-rose-500' : ($log->risk_score >= 50 ? 'bg-amber-500' : ($log->risk_score > 0 ? 'bg-emerald-500' : 'bg-slate-300')) }}"></span>
                                <span class="text-[10px] font-mono font-bold {{ $log->risk_score >= 80 ? 'text-rose-500' : ($log->risk_score >= 50 ? 'text-amber-500' : ($log->risk_score > 0 ? 'text-emerald-500' : 'text-slate-500')) }} tabular-nums">
                                    {{ $log->risk_score !== null ? $log->risk_score . '%' : '--' }}
                                </span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest {{ $decision['color'] }}">{{ $decision['label'] }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                            $statusStyles = [
                                'success' => 'border-emerald-200 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-400',
                                'failed' => 'border-rose-200 bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-400',
                                'blocked' => 'border-slate-800 bg-slate-900 text-white dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400',
                                'otp_required' => 'border-amber-200 bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-400',
                            ];
                        @endphp
                        <span class="inline-block px-2.5 py-0.5 border rounded-sm text-[9px] font-black uppercase tracking-widest {{ $statusStyles[$log->status] ?? 'border-indigo-200 bg-indigo-50 text-indigo-600' }}">
                            {{ str_replace('_', ' ', $log->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="viewLogDetails({{ $log->id }})" class="text-slate-400 hover:text-indigo-600 transition-colors p-1" title="Lihat Detail Log">
                            <i class="fa-solid fa-magnifying-glass-chart text-sm"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-400 italic text-xs font-medium">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                                <i class="fa-solid fa-inbox text-xl opacity-20"></i>
                            </div>
                            <p>Belum ada data log yang tercatat.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/10">
        {{ $logs->links() }}
    </div>
    @endif
</div>
