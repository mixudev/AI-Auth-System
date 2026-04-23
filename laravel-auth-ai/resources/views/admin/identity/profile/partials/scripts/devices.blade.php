// ─── Device Revoke ───────────────────────────────────────────────────────────
window.revokeDevice = function (deviceId, btn) {
    if (!window.AppPopup) return;
    AppPopup.confirm({
        title: 'Cabut Perangkat?',
        description: 'Perangkat ini tidak akan lagi dipercaya dan akan memerlukan verifikasi OTP kembali.',
        confirmText: 'Ya, Cabut',
        cancelText: 'Batal',
        onConfirm: async () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i>';

            try {
                const res = await fetch(`{{ url('dashboard/profile/devices') }}/${deviceId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                if (res.ok) {
                    const row = btn.closest('[data-device-id]');
                    if (row) {
                        row.style.transition = 'opacity 0.3s, transform 0.3s';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(16px)';
                        setTimeout(() => row.remove(), 300);
                    }
                } else {
                    const data = await res.json().catch(() => ({}));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-ban text-[10px]"></i> Cabut';
                    alert(data.message || 'Gagal mencabut perangkat.');
                }
            } catch (e) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-ban text-[10px]"></i> Cabut';
            }
        }
    });
};
