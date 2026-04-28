@if($tab === 'auth' && !empty($stats))
<!-- Auth Stats Section -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm p-4 shadow-sm">
        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Attempts</p>
        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 tabular-nums">{{ number_format($stats['total']) }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-0.5 h-full bg-emerald-500"></div>
        <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Success</p>
        <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ number_format($stats['success']) }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-0.5 h-full bg-rose-500"></div>
        <p class="text-[9px] font-bold text-rose-500 uppercase tracking-widest mb-1">Failed</p>
        <h3 class="text-xl font-bold text-rose-600 dark:text-rose-400 tabular-nums">{{ number_format($stats['failed']) }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-0.5 h-full bg-slate-800"></div>
        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Blocked</p>
        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 tabular-nums">{{ number_format($stats['blocked']) }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-sm p-4 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 left-0 w-0.5 h-full bg-amber-500"></div>
        <p class="text-[9px] font-bold text-amber-500 uppercase tracking-widest mb-1">MFA Req</p>
        <h3 class="text-xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">{{ number_format($stats['otp']) }}</h3>
    </div>
</div>
@endif
