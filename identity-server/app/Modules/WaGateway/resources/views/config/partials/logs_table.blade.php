<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h3 class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-widest">Log Aktivitas Terbaru</h3>
        <div class="flex items-center gap-2">
            <span id="logUpdateIndicator" class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse hidden shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
            <span class="text-[10px] text-slate-400 font-mono italic">Real-time update active</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-6 py-3 font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight text-[9px]">Waktu</th>
                    <th class="px-6 py-3 font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight text-[9px]">Gateway</th>
                    <th class="px-6 py-3 font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight text-[9px]">Tujuan</th>
                    <th class="px-6 py-3 font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight text-[9px]">Pesan</th>
                    <th class="px-6 py-3 font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight text-[9px] text-center">Status</th>
                    <th class="px-6 py-3 font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tight text-[9px] text-center">Detail</th>
                </tr>
            </thead>
            <tbody id="logTableBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($logs as $log)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-3 text-slate-400 font-mono whitespace-nowrap">{{ $log->sent_at->format('d/m H:i') }}</td>
                    <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300">
                        <span class="flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            {{ $log->config->name }}
                        </span>
                    </td>
                    <td class="px-6 py-3 font-mono text-slate-500">{{ $log->target_number }}</td>
                    <td class="px-6 py-3 max-w-xs truncate text-slate-400" title="{{ $log->message }}">{{ $log->message }}</td>
                    <td class="px-6 py-3 text-center text-[9px] font-bold">
                        @if($log->status === 'success')
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">SUCCESS</span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">FAILED</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center">
                        <button
                            type="button"
                            class="btn-log-detail p-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-colors"
                            title="Lihat Detail Log"
                            data-log='{!! json_encode(["status" => $log->status, "gateway" => $log->config->name ?? "-", "target" => $log->target_number, "message" => $log->message, "error_message" => $log->error_message, "response_id" => $log->response_id, "response_data" => $log->response_data, "sent_at" => optional($log->sent_at)->format("Y-m-d H:i:s")]) !!}'
                        >
                            <i class="fa-solid fa-eye text-[10px]"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Belum ada aktivitas tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
