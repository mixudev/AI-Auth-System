<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('sso.clients.index') }}"
               class="w-8 h-8 flex items-center justify-center rounded-sm bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h2 class="text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight leading-none">
                    Konfigurasi Akses
                </h2>
                <p class="text-[11px] text-slate-400 font-medium uppercase tracking-widest mt-1">
                    Client: <span class="text-indigo-500">{{ $client->name }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <div class="flex flex-col items-end mr-2 hidden md:flex">
            <span class="text-[10px] text-slate-400 font-bold uppercase">Status Client</span>
            @if($client->is_active)
                <span class="text-xs font-bold text-emerald-500 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ACTIVE
                </span>
            @else
                <span class="text-xs font-bold text-slate-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> INACTIVE
                </span>
            @endif
        </div>
        <div class="w-10 h-10 rounded-sm bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
            {{ strtoupper(substr($client->name, 0, 1)) }}
        </div>
    </div>
</div>
