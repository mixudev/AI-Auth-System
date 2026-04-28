<!-- Sidebar -->
<aside class="w-full lg:w-64 shrink-0">
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden sticky top-8">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            <h3 class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-address-card text-slate-400 dark:text-slate-500"></i> Data Akun
            </h3>
        </div>
        <div class="p-5 space-y-4 text-sm">
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Peran Akses</p>
                <p class="font-semibold text-slate-800 dark:text-slate-200">Administrator</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Bergabung Sejak</p>
                <p class="font-semibold text-slate-800 dark:text-slate-200">{{ Auth::user()->created_at->translatedFormat('d F Y') }}</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Zona Waktu</p>
                <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ Auth::user()->timezone ?? 'UTC' }}</p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">MFA</p>
                    @if(Auth::user()->mfa_enabled)
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">Aktif</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-slate-100 text-slate-500 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">Tidak Aktif</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">Email</p>
                    @if(Auth::user()->email_verified_at)
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">Terverifikasi</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-500 border border-amber-200 dark:border-amber-800/50">Belum</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</aside>
