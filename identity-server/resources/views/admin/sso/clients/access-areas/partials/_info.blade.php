@if(count($assignedIds) === 0)
<div class="group relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm p-5 shadow-sm overflow-hidden">
    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-sm border border-emerald-100 dark:border-emerald-500/20">
            <i class="fa-solid fa-lock-open text-lg"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm mb-1 uppercase tracking-tight">Public Access (Open Client)</h4>
            <p class="text-slate-500 dark:text-slate-400 text-xs leading-relaxed">
                Aplikasi ini saat ini bersifat <strong>terbuka</strong>. Siapa pun dengan akun aktif dapat masuk tanpa pembatasan area.
            </p>
        </div>
    </div>
</div>
@else
<div class="group relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm p-5 shadow-sm overflow-hidden">
    <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-sm border border-indigo-100 dark:border-indigo-500/20">
            <i class="fa-solid fa-shield-halved text-lg"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm mb-1 uppercase tracking-tight">Restricted Access Enabled</h4>
            <p class="text-slate-500 dark:text-slate-400 text-xs leading-relaxed">
                Akses ke aplikasi ini dibatasi. Pengguna wajib memiliki <strong>{{ count($assignedIds) }}</strong> wilayah akses yang telah ditentukan.
            </p>
        </div>
    </div>
</div>
@endif
