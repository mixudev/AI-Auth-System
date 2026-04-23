<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
        <div>
            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Total Gateway</p>
            <p class="text-xl font-bold text-slate-800 dark:text-white mt-1">{{ $stats['total_configs'] }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-500 ring-1 ring-indigo-100 dark:ring-indigo-500/20">
            <i class="fa-solid fa-server text-sm"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
        <div>
            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Gateway Aktif</p>
            <p class="text-xl font-bold text-emerald-500 mt-1">{{ $stats['active_configs'] }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500 ring-1 ring-emerald-100 dark:ring-emerald-500/20">
            <i class="fa-solid fa-signal text-sm"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
        <div>
            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Pesan Terkirim</p>
            <p class="text-xl font-bold text-sky-500 mt-1">{{ $stats['total_messages_sent'] }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center text-sky-500 ring-1 ring-sky-100 dark:ring-sky-500/20">
            <i class="fa-solid fa-paper-plane text-sm"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-xl flex items-center justify-between shadow-sm">
        <div>
            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Gagal Kirim</p>
            <p class="text-xl font-bold text-red-500 mt-1">{{ $stats['failed_messages'] }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-500 ring-1 ring-red-100 dark:ring-red-500/20">
            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
        </div>
    </div>
</div>
