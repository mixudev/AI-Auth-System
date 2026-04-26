<!-- Tab: Security -->
<section x-show="tab === 'security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
    <form id="form-security" action="{{ route('settings.configurations.update') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md shadow-sm">
        @csrf
        <input type="hidden" name="group" value="security">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Security Policy</h3>
            <p class="text-xs text-slate-400 mt-1">Kebijakan pengetatan akses dan proteksi akun user.</p>
        </div>
        <div class="p-8 space-y-8">
            <!-- Password Section -->
            <div class="space-y-6">
                <h4 class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest border-b border-indigo-100 dark:border-indigo-500/10 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-[8px]"></i> Password Complexity
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Min Length</label>
                        <input type="number" name="password_min_length" value="{{ $settings['password_min_length'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300">
                    </div>
                    <div class="space-y-4 pt-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="password_require_symbols" value="1" {{ $settings['password_require_symbols'] ? 'checked' : '' }} 
                                    class="w-5 h-5 rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 bg-white dark:bg-slate-950 transition-all">
                            </div>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Wajib Simbol (!@#$%)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="password_require_numbers" value="1" {{ $settings['password_require_numbers'] ? 'checked' : '' }} 
                                    class="w-5 h-5 rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 bg-white dark:bg-slate-950 transition-all">
                            </div>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Wajib Angka (0-9)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Brute Force Section -->
            <div class="space-y-6 pt-8 border-t border-slate-50 dark:border-slate-800/50">
                <h4 class="text-[10px] font-bold text-rose-500 uppercase tracking-widest border-b border-rose-100 dark:border-rose-500/10 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-[8px]"></i> Brute-Force Protection
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" value="{{ $settings['max_login_attempts'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300">
                        <p class="text-[9px] text-slate-400 italic font-medium">User akan diblokir sementara setelah batas ini.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Lockout Duration (Min)</label>
                        <input type="number" name="lockout_duration" value="{{ $settings['lockout_duration'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="button" @click="submitWithSudo('form-security')" class="px-6 py-2.5 bg-indigo-600 text-white rounded-md text-xs font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">Save Policy</button>
        </div>
    </form>
</section>
