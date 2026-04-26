<!-- Header & Tab Switcher -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Pusat Monitoring Log</h2>
        <p class="text-[11px] text-slate-500 font-medium">Pantau keamanan dan aktivitas seluruh sistem secara real-time.</p>
    </div>

    <!-- Segmented Control / Tab Switcher - Rounded SM -->
    <div class="inline-flex p-1 bg-slate-100/50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-sm">
        <button onclick="switchLogTab('auth')" id="tab-btn-auth"
                class="log-tab-btn px-5 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-sm transition-all {{ $tab === 'auth' ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-sm border border-slate-200 dark:border-slate-600 active-tab' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">
            Auth Logs
        </button>
        <button onclick="switchLogTab('audit')" id="tab-btn-audit"
                class="log-tab-btn px-5 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-sm transition-all {{ $tab === 'audit' ? 'bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-sm border border-slate-200 dark:border-slate-600 active-tab' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}">
            Audit Logs
        </button>
    </div>
</div>
