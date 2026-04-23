// ─── Global Initializers ───────────────────────────────────────────────────
window.initOtpCards = function () {
    const radios = document.querySelectorAll('.otp-radio');
    if (!radios.length) return;

    function applyOtpState() {
        radios.forEach(radio => {
            const val = radio.value;
            const box = document.querySelector(`[data-otp-box="${val}"]`);
            const icon = document.querySelector(`[data-otp-icon="${val}"]`);
            const lbl = document.querySelector(`[data-otp-label="${val}"]`);
            const on = radio.checked;
            if (!box) return;

            box.classList.toggle('border-violet-500', on);
            box.classList.toggle('bg-violet-50/50', on);
            box.classList.toggle('dark:bg-violet-900/10', on);
            box.classList.toggle('border-slate-200', !on);
            box.classList.toggle('dark:border-slate-700', !on);
            box.classList.toggle('bg-white', !on);
            box.classList.toggle('dark:bg-slate-800', !on);

            if (icon) {
                icon.classList.toggle('text-violet-500', on);
                icon.classList.toggle('text-slate-400', !on);
                icon.classList.toggle('dark:text-slate-500', !on);
            }
            if (lbl) {
                lbl.classList.toggle('text-violet-700', on);
                lbl.classList.toggle('dark:text-violet-400', on);
                lbl.classList.toggle('text-slate-700', !on);
                lbl.classList.toggle('dark:text-slate-300', !on);
            }
        });
    }

    radios.forEach(r => {
        if (!r.dataset.listenerBound) {
            r.addEventListener('change', applyOtpState);
            r.dataset.listenerBound = '1';
        }
    });
    applyOtpState();
};

// ─── Avatar Preview ──────────────────────────────────────────────────────────
window.previewImage = function (input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const el = document.getElementById('avatarPreview');
        if (el) el.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
};

// ─── Lupa Sandi ──────────────────────────────────────────────────────────────
window.confirmResetSandi = function () {
    AppPopup.confirm({
        title: 'Kirim Link Reset?',
        description: 'Link untuk mengatur ulang kata sandi akan dikirim ke email Anda.',
        confirmText: 'Ya, Kirim Link',
        cancelText: 'Batal',
        onConfirm: () => document.getElementById('reset-link-form').submit()
    });
};
