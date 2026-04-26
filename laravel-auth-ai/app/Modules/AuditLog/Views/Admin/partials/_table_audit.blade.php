<div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelaku</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Event Action</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">IP Address</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu Terjadi</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($logs as $log)
                <tr class="transition-colors duration-200">
                    <td class="px-6 py-4">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $log->user->name ?? 'System' }}</div>
                        <div class="text-[10px] text-slate-400 font-medium">{{ $log->user->email ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-[10px] font-mono font-bold text-slate-600 dark:text-slate-400 rounded-sm">
                            {{ $log->event }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs font-mono text-slate-500">{{ $log->ip_address }}</td>
                    <td class="px-6 py-4 text-[11px] text-slate-500 font-medium">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('audit-logs.show', $log->id) }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 text-[11px] font-bold uppercase tracking-wider transition-colors">
                            Detail <i class="fa-solid fa-chevron-right text-[9px]"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center text-slate-400 italic text-xs font-medium">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center">
                                <i class="fa-solid fa-inbox text-xl opacity-20"></i>
                            </div>
                            <p>Belum ada data log aktivitas.</p>
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
