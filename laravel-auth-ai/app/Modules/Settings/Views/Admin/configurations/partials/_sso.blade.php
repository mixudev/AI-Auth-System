<!-- Tab: SSO -->
<section x-show="tab === 'sso'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
    <form id="form-sso" action="{{ route('settings.configurations.update') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md shadow-sm">
        @csrf
        <input type="hidden" name="group" value="sso">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">SSO & Token Lifecycle</h3>
            <p class="text-xs text-slate-400 mt-1">Mengatur masa berlaku token akses untuk aplikasi terhubung.</p>
        </div>
        <div class="p-8 space-y-8">
            <div class="bg-amber-50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/10 p-4 rounded-md flex gap-4 items-start">
                <div class="w-8 h-8 rounded bg-amber-500/20 flex items-center justify-center text-amber-600 shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-800 dark:text-amber-400">Security Recommendation</h4>
                    <p class="text-[10px] text-amber-700/80 dark:text-amber-500/60 leading-relaxed mt-1">Gunakan waktu akses token yang singkat (60-120 menit) untuk meminimalkan risiko jika token bocor.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Access Token Lifetime (Menit)</label>
                    <div class="relative group">
                        <input type="number" name="token_expiry_access" value="{{ $settings['token_expiry_access'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300 pl-4 pr-12">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 tracking-wider">MIN</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Refresh Token Lifetime (Hari)</label>
                    <div class="relative group">
                        <input type="number" name="token_expiry_refresh" value="{{ $settings['token_expiry_refresh'] / 1440 }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300 pl-4 pr-12">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 tracking-wider">DAY</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="button" @click="submitWithSudo('form-sso')" class="px-6 py-2.5 bg-indigo-600 text-white rounded-md text-xs font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">Apply Policy</button>
        </div>
    </form>
</section>
