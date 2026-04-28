<!-- Tab: General -->
<section x-show="tab === 'general'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
    <form id="form-general" action="{{ route('settings.configurations.update') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md shadow-sm">
        @csrf
        <input type="hidden" name="group" value="general">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">General Information</h3>
            <p class="text-xs text-slate-400 mt-1">Konfigurasi dasar identitas aplikasi Anda.</p>
        </div>
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pt-2">Nama Aplikasi</label>
                <div class="md:col-span-2">
                    <input type="text" name="site_name" value="{{ $settings['site_name'] }}" 
                        class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300 placeholder:text-slate-400" 
                        required>
                    <p class="text-[10px] text-slate-400 mt-1.5 font-medium">Akan muncul di title browser dan sidebar.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start pt-6 border-t border-slate-50 dark:border-slate-800/50">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest pt-2">Deskripsi</label>
                <div class="md:col-span-2">
                    <textarea name="site_description" rows="4" 
                        class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300 placeholder:text-slate-400">{{ $settings['site_description'] }}</textarea>
                    <p class="text-[10px] text-slate-400 mt-1.5 font-medium">Meta deskripsi untuk kebutuhan SEO dan identitas sistem.</p>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="button" @click="submitWithSudo('form-general')" class="px-6 py-2.5 bg-indigo-600 text-white rounded-md text-xs font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">Save Changes</button>
        </div>
    </form>
</section>
