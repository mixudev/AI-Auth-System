<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm shadow-sm overflow-hidden sticky top-24">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
        <h3 class="text-xs font-black text-slate-800 dark:text-slate-100 uppercase tracking-widest">Ringkasan Sesi</h3>
    </div>
    <div class="p-5 space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-500 font-medium">Area Terpilih:</span>
            <span id="selectedCount" class="text-sm font-black text-indigo-600 dark:text-indigo-400 tabular-nums">{{ count($assignedIds) }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-500 font-medium">Total Tersedia:</span>
            <span class="text-sm font-bold text-slate-700 dark:text-slate-200 tabular-nums">{{ $allAreas->count() }}</span>
        </div>
        <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 pt-3">
            <span class="text-xs text-slate-500 font-medium">Mode Keamanan:</span>
            <span id="accessTypeLabel" class="text-[10px] font-black px-2 py-0.5 rounded-sm uppercase tracking-tighter {{ count($assignedIds) > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                {{ count($assignedIds) > 0 ? 'RESTRICTED' : 'OPEN' }}
            </span>
        </div>
        
        <div class="pt-2">
            <button type="button" onclick="confirmSync()"
                    class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 text-xs font-black rounded-sm transition-all shadow-md shadow-indigo-500/20 uppercase tracking-widest">
                <i class="fa-solid fa-cloud-arrow-up"></i> Terapkan
            </button>
            <a href="{{ route('sso.clients.index') }}"
               class="w-full mt-2 inline-flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 px-4 py-2 text-[10px] font-bold rounded-sm transition-all uppercase tracking-widest">
                Batal
            </a>
        </div>
    </div>
    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
        <p class="text-[10px] text-slate-400 italic leading-snug">
            <i class="fa-solid fa-circle-info mr-1"></i> Perubahan akan langsung berdampak pada alur login client.
        </p>
    </div>
</div>
