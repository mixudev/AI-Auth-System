{{-- CREATE MODAL --}}
<x-app-modal id="createModal" title="Tambah Gateway Baru" description="Pilih fungsi gateway untuk mengintegrasikan sistem dengan WhatsApp." icon="<i class='fa-solid fa-plus text-xs'></i>" iconColor="emerald" maxWidth="md">
    <div class="space-y-5">
        <div id="createError" class="hidden px-3 py-2.5 bg-red-50 dark:bg-red-500/10 text-red-500 text-[10px] rounded-xl border border-red-200 dark:border-red-500/20 font-bold">
            <span id="createErrorMsg"></span>
        </div>

        <div>
            <label for="createName" class="mb-1.5 block">Nama Konfigurasi</label>
            <input type="text" id="createName" placeholder="Misal: Gateway Notifikasi Utama" required class="w-full">
        </div>
        
        <div>
            <label for="createPurpose" class="mb-1.5 block">Tujuan Penggunaan (Function)</label>
            <select id="createPurpose" class="w-full">
                <option value="security">Alert Keamanan (Login Failure, Brute Force, etc.)</option>
                <option value="auth">Autentikasi (MFA, OTP, Reset Password)</option>
                <option value="info">Informasi (Pengumuman, Welcome Message)</option>
                <option value="system">Sistem (Log, Monitoring, Other)</option>
            </select>
            <p class="mt-1.5 text-[10px] text-slate-400">Fungsi ini menentukan bagaimana sistem memilih gateway untuk mengirim pesan.</p>
        </div>

        <div>
            <label for="createToken" class="mb-1.5 block">Fontte API Token</label>
            <input type="password" id="createToken" placeholder="Masukkan token API Fontte..." required class="w-full">
        </div>

        <div>
            <label for="createAlertNumber" class="mb-1.5 block">Nomor Penerima (Default)</label>
            <input type="text" id="createAlertNumber" placeholder="Contoh: 628123456789" required class="w-full font-mono">
            <p class="mt-1.5 text-[10px] text-slate-400 italic">Format: 628... (tanpa tanda +)</p>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('createModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitCreate()" id="createSubmitBtn" class="modal-btn-primary">
            <span id="createSpinner" class="hidden mr-2"><i class="fa-solid fa-spinner fa-spin"></i></span>
            Simpan Konfigurasi
        </button>
    </x-slot>
</x-app-modal>

{{-- INFO & RISK MODAL --}}
<x-app-modal id="infoModal" title="Gateway Info & Risk Awareness" description="Panduan penggunaan aman dan konfigurasi sistem WhatsApp Gateway." icon="<i class='fa-solid fa-shield-heart text-xs'></i>" iconColor="emerald" maxWidth="lg">
    <div class="space-y-6">
        {{-- Section 1: System Overview --}}
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2 mb-2">
                <i class="fa-solid fa-network-wired text-indigo-500"></i>
                Apa itu Operations Center?
            </h4>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                Ini adalah pusat kendali terpadu untuk seluruh rute WhatsApp di aplikasi Anda. Sistem menggunakan **Smart Routing** untuk memilih jalur terbaik secara otomatis berdasarkan fungsi pesan (Security, Auth, atau Info).
            </p>
        </div>

        {{-- Section 2: BAN RISK WARNING --}}
        <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-500/5 border border-amber-200 dark:border-amber-500/20">
            <h4 class="text-xs font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2 mb-3">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Peringatan Risiko (Anti-Ban)
            </h4>
            <div class="space-y-3">
                <div class="flex gap-3">
                    <div class="w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 flex items-center justify-center shrink-0">
                        <span class="text-[10px] font-bold">1</span>
                    </div>
                    <p class="text-[10px] text-amber-800/70 dark:text-amber-400/80">
                        **Jangan Spam**: WhatsApp sangat ketat terhadap pesan massal. Pastikan pesan hanya dikirim ke user yang memang membutuhkan informasi tersebut (OTP/Alert).
                    </p>
                </div>
                <div class="flex gap-3">
                    <div class="w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 flex items-center justify-center shrink-0">
                        <span class="text-[10px] font-bold">2</span>
                    </div>
                    <p class="text-[10px] text-amber-800/70 dark:text-amber-400/80">
                        **Gunakan Guardrail**: Kami telah menyediakan fitur *Daily Limit* dan *Quiet Hours*. **Sangat disarankan** untuk mengaktifkannya guna menghindari aktivitas yang mencurigakan di mata sistem WhatsApp.
                    </p>
                </div>
                <div class="flex gap-3">
                    <div class="w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 flex items-center justify-center shrink-0">
                        <span class="text-[10px] font-bold">3</span>
                    </div>
                    <p class="text-[10px] text-amber-800/70 dark:text-amber-400/80">
                        **Fonnte vs Official**: Fonnte lebih fleksibel namun memiliki risiko ban lebih tinggi jika disalahgunakan. Jalur Official lebih aman namun memerlukan proses verifikasi bisnis.
                    </p>
                </div>
            </div>
        </div>

        {{-- Section 3: Configuration Tips --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50">
                <h5 class="text-[10px] font-bold text-slate-700 dark:text-slate-200 uppercase mb-2">Smart Failover</h5>
                <p class="text-[9px] text-slate-400 leading-relaxed">Sistem akan otomatis mencoba jalur alternatif jika pengiriman via jalur utama gagal dalam 3 kali percobaan.</p>
            </div>
            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50">
                <h5 class="text-[10px] font-bold text-slate-700 dark:text-slate-200 uppercase mb-2">Audit Logs</h5>
                <p class="text-[9px] text-slate-400 leading-relaxed">Setiap pesan yang keluar dicatat secara detail. Gunakan tab Logs untuk audit keamanan dan debugging.</p>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('infoModal')" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition-all">
            Saya Mengerti & Lanjutkan
        </button>
    </x-slot>
</x-app-modal>

{{-- TEMPLATE MODAL --}}
<x-app-modal id="templateModal" title="Manajemen Template" description="Buat dan perbarui template pesan WhatsApp sistem." icon="<i class='fa-solid fa-file-invoice text-xs'></i>" iconColor="indigo" maxWidth="lg">
    <div class="space-y-5" x-data="{ 
        tplName: '', 
        tplContent: '',
        get preview() {
            return this.tplContent.replace(/{(\w+)}/g, '<span class=\'text-indigo-500 font-bold\'>{$1}</span>');
        }
    }">
        <input type="hidden" id="tplId">
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="tplName" class="mb-1.5 block">Nama Template</label>
                <input type="text" id="tplName" x-model="tplName" placeholder="Misal: Alert Login Kritis" class="w-full">
            </div>
            <div>
                <label for="tplSlug" class="mb-1.5 block">Slug (Unique Key)</label>
                <input type="text" id="tplSlug" placeholder="login-alert-critical" class="w-full font-mono">
            </div>
        </div>

        <div>
            <label for="tplPurpose" class="mb-1.5 block">Tujuan Penggunaan</label>
            <select id="tplPurpose" class="w-full">
                <option value="security">Security Alert</option>
                <option value="auth">Authentication (OTP/MFA)</option>
                <option value="info">Information/Broadcast</option>
                <option value="system">System Monitoring</option>
            </select>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="tplContent">Konten Pesan</label>
                <span class="text-[10px] text-slate-400 font-mono">Variables: {event}, {time}, {ip}, {user}</span>
            </div>
            <textarea id="tplContent" x-model="tplContent" rows="5" class="w-full font-mono text-xs" placeholder="Masukkan isi pesan di sini..."></textarea>
        </div>

        {{-- LIVE PREVIEW --}}
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10 text-white">
                <i class="fa-brands fa-whatsapp text-4xl"></i>
            </div>
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                WhatsApp Live Preview
            </p>
            <div class="bg-slate-800/50 rounded-xl p-4 text-xs text-slate-300 leading-relaxed font-sans border-l-4 border-emerald-500">
                <div x-html="preview || '<span class=\'opacity-30 italic\'>Menunggu input konten...</span>'"></div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('templateModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitTemplate()" id="tplSubmitBtn" class="modal-btn-primary">
            <span id="tplSpinner" class="hidden mr-2"><i class="fa-solid fa-spinner fa-spin"></i></span>
            Simpan Template
        </button>
    </x-slot>
</x-app-modal>

{{-- EDIT MODAL --}}
<x-app-modal id="editModal" title="Edit Gateway" description="Perbarui detail konfigurasi dan fungsi gateway ini." icon="<i class='fa-solid fa-pen-to-square text-xs'></i>" iconColor="amber" maxWidth="md">
    <div class="space-y-5">
        <input type="hidden" id="editConfigId">
        
        <div id="editError" class="hidden px-3 py-2.5 bg-red-50 dark:bg-red-500/10 text-red-500 text-[10px] rounded-xl border border-red-200 dark:border-red-500/20 font-bold">
            <span id="editErrorMsg"></span>
        </div>

        <div>
            <label for="editName" class="mb-1.5 block">Nama Konfigurasi</label>
            <input type="text" id="editName" required class="w-full">
        </div>

        <div>
            <label for="editPurpose" class="mb-1.5 block">Tujuan Penggunaan (Function)</label>
            <select id="editPurpose" class="w-full">
                <option value="security">Alert Keamanan (Login Failure, Brute Force, etc.)</option>
                <option value="auth">Autentikasi (MFA, OTP, Reset Password)</option>
                <option value="info">Informasi (Pengumuman, Welcome Message)</option>
                <option value="system">Sistem (Log, Monitoring, Other)</option>
            </select>
        </div>

        <div>
            <label for="editToken" class="mb-1.5 block">Fontte API Token</label>
            <input type="password" id="editToken" placeholder="Biarkan kosong jika tidak ingin mengubah token" class="w-full">
        </div>

        <div>
            <label for="editAlertNumber" class="mb-1.5 block">Nomor Penerima (Default)</label>
            <input type="text" id="editAlertNumber" required class="w-full font-mono">
        </div>

        <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
            <div class="pt-0.5">
                <input type="checkbox" id="editIsActive" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            </div>
            <div>
                <label for="editIsActive" class="mb-0 text-slate-700 dark:text-slate-300 font-bold normal-case tracking-normal">Status Gateway</label>
                <p class="text-[10px] text-slate-400 mt-0.5">Jika nonaktif, gateway ini tidak akan digunakan oleh sistem.</p>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('editModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitEdit()" id="editSubmitBtn" class="modal-btn-primary">
            <span id="editSpinner" class="hidden mr-2"><i class="fa-solid fa-spinner fa-spin"></i></span>
            Update Gateway
        </button>
    </x-slot>
</x-app-modal>

{{-- SYSTEM CONFIG MODAL --}}
<x-app-modal id="systemConfigModal" title="Global System Settings" description="Konfigurasi provider utama dan pengaturan keamanan sistem." icon="<i class='fa-solid fa-gears text-xs'></i>" iconColor="indigo" maxWidth="xl">
    <form id="systemConfigForm" onsubmit="event.preventDefault(); submitSystemConfig();" 
          x-data="{ 
            provider: '{{ data_get($systemSettings, 'provider', 'fonnte') }}',
            tab: 'provider'
          }" class="space-y-6">
        
        {{-- TAB NAVIGATION --}}
        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800/50 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
            <button type="button" @click="tab = 'provider'" :class="tab === 'provider' ? 'bg-white dark:bg-slate-700 text-indigo-500 shadow-sm' : 'text-slate-400'" class="flex-1 py-1.5 text-[10px] font-bold uppercase rounded-lg transition-all">
                Provider Settings
            </button>
            <button type="button" @click="tab = 'guardrail'" :class="tab === 'guardrail' ? 'bg-white dark:bg-slate-700 text-indigo-500 shadow-sm' : 'text-slate-400'" class="flex-1 py-1.5 text-[10px] font-bold uppercase rounded-lg transition-all">
                Safety Guardrail
            </button>
        </div>

        <div x-show="tab === 'provider'" x-transition>
            {{-- STEP 1: PROVIDER SELECTION --}}
            <div class="space-y-4">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pilih Provider Aktif</label>
                <div class="grid grid-cols-2 gap-4">
                    {{-- Fonnte Card --}}
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="provider" value="fonnte" x-model="provider" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 group-hover:border-indigo-200 dark:group-hover:border-indigo-500/30 peer-checked:border-indigo-500 peer-checked:ring-4 peer-checked:ring-indigo-500/10 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                                    <i class="fa-solid fa-bolt text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Fonnte</p>
                                    <p class="text-[10px] text-slate-400">High Performance</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-3 right-3 hidden peer-checked:block text-indigo-500">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </label>

                    {{-- Official Card --}}
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="provider" value="official" x-model="provider" class="peer sr-only">
                        <div class="p-4 rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 group-hover:border-indigo-200 dark:group-hover:border-indigo-500/30 peer-checked:border-indigo-500 peer-checked:ring-4 peer-checked:ring-indigo-500/10 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                                    <i class="fa-solid fa-building-columns text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Official</p>
                                    <p class="text-[10px] text-slate-400">Stable & Secure</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-3 right-3 hidden peer-checked:block text-emerald-500">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </label>
                </div>
            </div>

            {{-- STEP 2: PROVIDER FIELDS --}}
            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
                {{-- Fonnte Fields --}}
                <div x-show="provider === 'fonnte'" x-transition class="space-y-4">
                    <div>
                        <label>Base URL</label>
                        <input type="text" name="providers[fonnte][base_url]" value="{{ data_get($systemSettings, 'providers.fonnte.base_url') }}" placeholder="https://api.fonnte.com" class="w-full">
                    </div>
                    <div>
                        <label>API Token</label>
                        <input type="password" name="providers[fonnte][token]" value="{{ data_get($systemSettings, 'providers.fonnte.token') }}" placeholder="Enter Fonnte token" class="w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label>Token Header</label>
                            <input type="text" name="providers[fonnte][token_header]" value="{{ data_get($systemSettings, 'providers.fonnte.token_header', 'Authorization') }}" class="w-full">
                        </div>
                        <div>
                            <label>Token Prefix</label>
                            <input type="text" name="providers[fonnte][token_prefix]" value="{{ data_get($systemSettings, 'providers.fonnte.token_prefix') }}" class="w-full">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label>Timeout (Sec)</label>
                            <input type="number" name="providers[fonnte][timeout]" value="{{ data_get($systemSettings, 'providers.fonnte.timeout', 15) }}" class="w-full">
                        </div>
                        <div>
                            <label>Country Code</label>
                            <input type="text" name="providers[fonnte][default_country_code]" value="{{ data_get($systemSettings, 'providers.fonnte.default_country_code', '62') }}" class="w-full">
                        </div>
                    </div>
                </div>

                {{-- Official Fields --}}
                <div x-show="provider === 'official'" x-transition class="space-y-4">
                    <div>
                        <label>Base URL</label>
                        <input type="text" name="providers[official][base_url]" value="{{ data_get($systemSettings, 'providers.official.base_url') }}" placeholder="https://official-api.com" class="w-full">
                    </div>
                    <div>
                        <label>API Token</label>
                        <input type="password" name="providers[official][token]" value="{{ data_get($systemSettings, 'providers.official.token') }}" placeholder="Enter official token" class="w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label>Token Header</label>
                            <input type="text" name="providers[official][token_header]" value="{{ data_get($systemSettings, 'providers.official.token_header', 'Authorization') }}" class="w-full">
                        </div>
                        <div>
                            <label>Token Prefix</label>
                            <input type="text" name="providers[official][token_prefix]" value="{{ data_get($systemSettings, 'providers.official.token_prefix', 'Bearer ') }}" class="w-full">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label>Timeout (Sec)</label>
                            <input type="number" name="providers[official][timeout]" value="{{ data_get($systemSettings, 'providers.official.timeout', 15) }}" class="w-full">
                        </div>
                        <div>
                            <label>Country Code</label>
                            <input type="text" name="providers[official][default_country_code]" value="{{ data_get($systemSettings, 'providers.official.default_country_code', '62') }}" class="w-full">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GUARDRAIL SETTINGS --}}
        <div x-show="tab === 'guardrail'" x-transition class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <div class="flex items-start gap-3">
                        <div class="pt-0.5">
                            <input type="checkbox" id="sysGuardEnabled" name="guardrail[enabled]" @checked((bool) data_get($systemSettings, 'guardrail.enabled')) class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="sysGuardEnabled" class="mb-0 text-slate-700 dark:text-slate-200 font-bold normal-case tracking-normal">Aktifkan Guardrail</label>
                            <p class="text-[10px] text-slate-400 mt-1">Mencegah spam dan deteksi bot (Anti-Ban).</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <div class="flex items-start gap-3">
                        <div class="pt-0.5">
                            <input type="checkbox" id="sysGuardAllowCritical" name="guardrail[allow_critical_in_quiet_hours]" @checked((bool) data_get($systemSettings, 'guardrail.allow_critical_in_quiet_hours')) class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="sysGuardAllowCritical" class="mb-0 text-slate-700 dark:text-slate-200 font-bold normal-case tracking-normal">Bypass Jam Istirahat</label>
                            <p class="text-[10px] text-slate-400 mt-1">Tetap kirim alert kritis meski di jam istirahat.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>Limit Harian / Config</label>
                    <input type="number" name="guardrail[daily_limit_per_config]" value="{{ data_get($systemSettings, 'guardrail.daily_limit_per_config', 100) }}" class="w-full">
                </div>
                <div>
                    <label>Cegah Duplikat (Detik)</label>
                    <input type="number" name="guardrail[prevent_duplicate_within_seconds]" value="{{ data_get($systemSettings, 'guardrail.prevent_duplicate_within_seconds', 300) }}" class="w-full">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label>Quiet Start</label>
                    <input type="text" name="guardrail[quiet_hours_start]" value="{{ data_get($systemSettings, 'guardrail.quiet_hours_start', '22:00') }}" placeholder="22:00" class="w-full font-mono">
                </div>
                <div>
                    <label>Quiet End</label>
                    <input type="text" name="guardrail[quiet_hours_end]" value="{{ data_get($systemSettings, 'guardrail.quiet_hours_end', '06:00') }}" placeholder="06:00" class="w-full font-mono">
                </div>
                <div>
                    <label>Random Delay (Sec)</label>
                    <input type="text" name="guardrail[default_random_delay]" value="{{ data_get($systemSettings, 'guardrail.default_random_delay', '3-8') }}" placeholder="3-8" class="w-full font-mono text-center">
                </div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button onclick="AppModal.close('systemConfigModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitSystemConfig()" id="sysConfigSubmitBtn" class="modal-btn-primary">
            <span id="sysConfigSpinner" class="hidden mr-2"><i class="fa-solid fa-spinner fa-spin"></i></span>
            Simpan Perubahan
        </button>
    </x-slot>
</x-app-modal>
