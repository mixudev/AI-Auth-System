<!-- Tab: Mail -->
<section x-show="tab === 'mail'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
    <form id="form-mail" action="{{ route('settings.configurations.update') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md shadow-sm">
        @csrf
        <input type="hidden" name="group" value="mail">
        <div class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Mail Server Config</h3>
                <p class="text-xs text-slate-400 mt-1">Konfigurasi SMTP untuk pengiriman OTP & notifikasi.</p>
            </div>
            <i class="fa-solid fa-server text-slate-200 dark:text-slate-800 text-3xl"></i>
        </div>
        <div class="p-8 space-y-8">
            <!-- Server Connection -->
            <div class="space-y-6">
                <h4 class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest border-b border-indigo-100 dark:border-indigo-500/10 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-link text-[8px]"></i> Connection Settings
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">SMTP Host</label>
                        <input type="text" name="mail_host" value="{{ $settings['mail_host'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300 placeholder:text-slate-400" 
                            placeholder="smtp.mailtrap.io">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">SMTP Port</label>
                        <input type="number" name="mail_port" value="{{ $settings['mail_port'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300 placeholder:text-slate-400" 
                            placeholder="587">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Encryption</label>
                        <select name="mail_encryption" class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300">
                            <option value="" {{ $settings['mail_encryption'] == '' ? 'selected' : '' }}>None</option>
                            <option value="tls" {{ $settings['mail_encryption'] == 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ $settings['mail_encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Authentication -->
            <div class="space-y-6 pt-8 border-t border-slate-50 dark:border-slate-800/50">
                <h4 class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest border-b border-emerald-100 dark:border-emerald-500/10 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-user-lock text-[8px]"></i> Authentication
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Username</label>
                        <input type="text" name="mail_username" value="{{ $settings['mail_username'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Password</label>
                        <input type="password" name="mail_password" value="{{ $settings['mail_password'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300">
                        @if(!empty($settings['mail_password']))
                        <p class="text-[9px] text-emerald-500 font-bold mt-1"><i class="fa-solid fa-circle-check"></i> Password tersimpan & terenkripsi</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sender Info -->
            <div class="space-y-6 pt-8 border-t border-slate-50 dark:border-slate-800/50">
                <h4 class="text-[10px] font-bold text-amber-500 uppercase tracking-widest border-b border-amber-100 dark:border-amber-500/10 pb-2 flex items-center gap-2">
                    <i class="fa-solid fa-address-card text-[8px]"></i> Sender Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">From Address</label>
                        <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300" 
                            placeholder="no-reply@campus.test">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">From Name</label>
                        <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] }}" 
                            class="w-full px-4 py-2.5 rounded-md border-slate-200 dark:border-slate-800 dark:bg-slate-950 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-300" 
                            placeholder="MixuAuth System">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <button type="button" onclick="testMailConnection()" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-md text-xs font-bold hover:bg-slate-300 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-paper-plane text-[10px]"></i> Test Connection
            </button>
            <button type="button" @click="submitWithSudo('form-mail')" class="px-6 py-2.5 bg-indigo-600 text-white rounded-md text-xs font-bold shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">Update Server</button>
        </div>
    </form>
</section>

<script>
function testMailConnection() {
    const form = document.getElementById('form-mail');
    const formData = new FormData(form);
    
    // Sembunyikan popup lama jika ada
    AppPopup.close();
    
    // Tampilkan loading
    AppPopup.info({
        title: 'Menghubungkan...',
        description: 'Sistem sedang mencoba mengirim email uji coba ke alamat Anda. Mohon tunggu...',
        showButton: false
    });

    fetch("{{ route('settings.configurations.test-mail') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(res => {
        AppPopup.close();
        if (res.status === 200 && res.body.success) {
            AppPopup.success({
                title: 'Berhasil!',
                description: res.body.message
            });
        } else {
            AppPopup.error({
                title: 'Koneksi Gagal',
                description: res.body.message || 'Terjadi kesalahan sistem.'
            });
        }
    })
    .catch(error => {
        AppPopup.close();
        AppPopup.error({
            title: 'Error',
            description: 'Gagal menghubungi server: ' + error.message
        });
    });
}
</script>
