// ─── MFA Setup ───────────────────────────────────────────────────────────────
async function openMfaSetupPopup() {
    if (!window.AppPopup?.show) return;

    AppPopup.show({
        type: 'info',
        title: 'Setup Authenticator',
        hideIcon: true,
        showButton: false,
        description: `
            <div class="text-left space-y-4 mt-2">
                <div id="popupMfaQr" class="mx-auto w-44 h-44 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="animate-spin h-7 w-7 border-2 border-slate-400 border-t-transparent rounded-full"></div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Manual Secret Key</p>
                    <code id="popupMfaSecret" class="block text-xs font-mono font-bold text-slate-700 dark:text-slate-200 tracking-widest">--------</code>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kode Verifikasi (6 Digit)</label>
                    <input type="text" id="popupMfaCode" maxlength="6" placeholder="000000"
                        class="w-full h-11 px-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-center font-mono text-lg tracking-[0.3em] focus:ring-1 focus:ring-violet-500 focus:border-violet-500 outline-none">
                    <p id="popupMfaError" class="hidden text-[10px] text-red-500 mt-1 font-semibold"></p>
                </div>
                <button type="button" id="popupMfaSubmit"
                    class="w-full h-11 rounded-lg bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold transition-all">
                    Verifikasi & Aktifkan
                </button>
            </div>
        `
    });

    const qrEl = document.getElementById('popupMfaQr');
    const secEl = document.getElementById('popupMfaSecret');
    const inputEl = document.getElementById('popupMfaCode');
    const submitEl = document.getElementById('popupMfaSubmit');

    if (submitEl) submitEl.addEventListener('click', window.confirmMfaSetupAction);

    try {
        const res = await fetch('{{ route("dashboard.profile.mfa.setup") }}');
        const data = await res.json();
        if (!res.ok) throw new Error(data?.message || 'Setup failed');
        if (qrEl) qrEl.innerHTML = data.qr_code;
        if (secEl) secEl.textContent = data.secret;
        if (inputEl) inputEl.focus();
    } catch (e) {
        AppPopup.error({ title: 'Gagal', description: 'Gagal mengambil data setup MFA.' });
    }
}

window.startMfaSetup = function () { openMfaSetupPopup(); };

window.confirmMfaSetupAction = async function () {
    const inputEl = document.getElementById('popupMfaCode');
    const btn = document.getElementById('popupMfaSubmit');
    const err = document.getElementById('popupMfaError');
    const code = (inputEl?.value || '').replace(/\s+/g, '');

    if (!inputEl || !btn || !err) return;
    if (code.length < 6) { err.textContent = 'Masukkan 6 digit kode lengkap.'; err.classList.remove('hidden'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Memvalidasi...';
    err.classList.add('hidden');

    try {
        const res = await fetch('{{ route("dashboard.profile.mfa.confirm") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ code })
        });
        const data = await res.json();

        if (res.ok) {
            AppPopup.show({
                type: 'success',
                title: 'MFA Berhasil Diaktifkan',
                showButton: false,
                description: `
                    <p class="text-xs text-slate-500 mb-3">Simpan backup code berikut di tempat aman:</p>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        ${data.backup_codes.map(c => `<div class="text-[10px] font-mono font-bold text-slate-700 dark:text-slate-300 py-1.5 px-2 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 text-center">${c}</div>`).join('')}
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick='window.downloadBackupCodes(${JSON.stringify(JSON.stringify(data.backup_codes))})' class="flex-1 h-10 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold flex items-center justify-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                            <i class="fa-solid fa-download text-[10px]"></i> Simpan Kode
                        </button>
                        <button type="button" onclick="window.location.reload()" class="flex-1 h-10 rounded-lg bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white text-xs font-semibold">Selesai</button>
                    </div>
                `
            });
        } else {
            err.textContent = data.message || 'Kode salah.';
            err.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Verifikasi & Aktifkan';
        }
    } catch (e) {
        err.textContent = 'Terjadi kesalahan sistem.';
        err.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Verifikasi & Aktifkan';
    }
};

window.downloadBackupCodes = function(codesJson) {
    const codes = JSON.parse(codesJson);
    const text = "BACKUP CODES - AI AUTH SYSTEM\n" + 
                 "Generated: " + new Date().toLocaleString() + "\n\n" + 
                 codes.join("\n") + "\n\n" + 
                 "Simpan kode ini di tempat yang aman. Setiap kode hanya dapat digunakan sekali.";
    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'backup-codes.txt';
    a.click();
    window.URL.revokeObjectURL(url);
};

// ─── MFA Disable ─────────────────────────────────────────────────────────────
window.openDisableMfaModal = function () {
    if (!window.AppPopup) return;
    AppPopup.show({
        type: 'warning',
        title: 'Matikan MFA?',
        hideIcon: false,
        showButton: false,
        description: `
            <div class="text-left space-y-4 mt-2">
                <p class="text-xs text-slate-500">Konfirmasi dengan kata sandi Anda untuk melanjutkan penonaktifan MFA.</p>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kata Sandi</label>
                    <input type="password" id="popupMfaDisablePw" placeholder="••••••••"
                        class="w-full h-11 px-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                    <p id="popupMfaDisableError" class="hidden text-[10px] text-red-500 mt-1 font-semibold"></p>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" onclick="AppPopup.close()" class="flex-1 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-xs font-semibold">Batal</button>
                    <button type="button" id="popupMfaDisableSubmit" class="flex-1 h-10 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-sm transition-all flex items-center justify-center">Ya, Matikan</button>
                </div>
            </div>
        `
    });

    setTimeout(() => {
        const btn = document.getElementById('popupMfaDisableSubmit');
        if (btn) btn.addEventListener('click', window.confirmDisableMfaAction);
    }, 10);
};

window.confirmDisableMfaAction = async function () {
    const pw = document.getElementById('popupMfaDisablePw')?.value;
    const err = document.getElementById('popupMfaDisableError');
    const btn = document.getElementById('popupMfaDisableSubmit');

    if (!pw) { 
        if (err) { err.textContent = 'Kata sandi wajib diisi.'; err.classList.remove('hidden'); }
        return; 
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>...';
    }
    if (err) err.classList.add('hidden');

    try {
        const res = await fetch('{{ route("dashboard.profile.mfa.disable") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ current_password: pw })
        });
        const data = await res.json();

        if (res.ok) {
            window.location.reload();
        } else {
            if (err) { err.textContent = data.message || 'Gagal menonaktifkan MFA.'; err.classList.remove('hidden'); }
            if (btn) { btn.disabled = false; btn.textContent = 'Ya, Matikan'; }
        }
    } catch (e) {
        if (err) { err.textContent = 'Terjadi kesalahan sistem.'; err.classList.remove('hidden'); }
        if (btn) { btn.disabled = false; btn.textContent = 'Ya, Matikan'; }
    }
};
