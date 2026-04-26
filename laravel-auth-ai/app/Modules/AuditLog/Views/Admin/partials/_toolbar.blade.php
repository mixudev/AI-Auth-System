<!-- Toolbar -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800 rounded-sm shadow-sm">
    <form action="{{ route('audit-logs.center') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
        <input type="hidden" name="tab" value="{{ $tab }}">
        
        <div class="relative w-full sm:w-64">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="{{ $tab === 'auth' ? 'Email atau IP...' : 'Event, User, atau IP...' }}" 
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-sm text-xs focus:ring-0 focus:border-slate-400 transition-all font-medium">
        </div>

        @if($tab === 'auth')
        <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-sm text-xs focus:ring-0 focus:border-slate-400 transition-all text-slate-600 dark:text-slate-300 font-bold">
            <option value="">Semua Status</option>
            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>SUCCESS</option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>FAILED</option>
            <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>BLOCKED</option>
            <option value="otp_required" {{ request('status') == 'otp_required' ? 'selected' : '' }}>MFA REQUIRED</option>
        </select>
        @endif

        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-slate-900 transition-all shadow-md shadow-slate-900/10">Filter</button>
        
        @if(request('search') || request('status'))
        <a href="{{ route('audit-logs.center', ['tab' => $tab]) }}" class="text-rose-500 hover:text-rose-600 text-xs font-bold uppercase tracking-tighter">Reset</a>
        @endif
    </form>

    @if($tab === 'auth')
    <div class="flex items-center gap-3">
        <button onclick="openBulkDeleteLogsModal()" class="px-4 py-2 border border-rose-200 dark:border-rose-500/30 text-rose-600 dark:text-rose-400 text-[10px] font-bold uppercase tracking-widest rounded-sm bg-rose-50/50 dark:bg-rose-500/5 hover:bg-rose-100 transition-all flex items-center gap-2">
            <i class="fa-solid fa-trash-can text-[11px]"></i> Bersihkan Log
        </button>
    </div>
    @endif
</div>
