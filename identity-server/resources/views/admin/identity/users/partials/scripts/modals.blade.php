// ── Global Actions (Verification) ─────────────────────────────────────────
window.sendVerificationEmailFromEdit = function (btn) {
    var id = document.getElementById('editUserId').value;
    if (!id) return;
    
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Mengirim...';

    api('POST', ROUTES.verifyEmail(id)).then(function(res){
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        showToast(res.status === 'success' ? 'success' : 'info', res.message);
    }).catch(function(){
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        showToast('error', 'Terjadi kesalahan sistem.');
    });
};

window.sendVerificationEmailAction = function (userId, email, btn) {
    AppPopup.info({
        title: 'Kirim Verifikasi?',
        description: 'Kirim link verifikasi email ke ' + email + '?',
        confirmText: 'Kirim Link',
        cancelText: 'Batal',
        onConfirm: function() {
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';

            api('POST', ROUTES.verifyEmail(userId)).then(function(res){
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showToast(res.status === 'success' ? 'success' : 'info', res.message);
            }).catch(function(){
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showToast('error', 'Terjadi kesalahan sistem.');
            });
        }
    });
};

// ── CREATE ────────────────────────────────────────────────────────────────
window.openCreateModal = function () {
    ['createName','createEmail','createPassword'].forEach(function(id){ 
        var el = document.getElementById(id);
        if(el) el.value = ''; 
    });
    var rolesEl = document.getElementById('createRoles');
    if(rolesEl) {
        Array.from(rolesEl.options).forEach(opt => opt.selected = false);
    }
    // Sync Alpine
    setTimeout(function() {
        window.dispatchEvent(new CustomEvent('set-createRoles-roles', { detail: [] }));
    }, 50);

    var activeEl = document.getElementById('createIsActive');
    if(activeEl) activeEl.value = '1';
    hideError('createError');
    updateCreatePreview();
    AppModal.open('createModal');
};

window.updateCreatePreview = function () {
    var nameEl = document.getElementById('createName');
    var emailEl = document.getElementById('createEmail');
    var name  = (nameEl ? nameEl.value : '') || 'Nama Pengguna';
    var email = (emailEl ? emailEl.value : '') || 'email@domain.com';
    
    var previewName = document.getElementById('createPreviewName');
    var previewEmail = document.getElementById('createPreviewEmail');
    var avatar = document.getElementById('createAvatar');

    if(previewName) previewName.textContent = name;
    if(previewEmail) previewEmail.textContent = email;
    if(avatar) avatar.textContent = name.charAt(0).toUpperCase();
};

window.submitCreate = function () {
    var name = (document.getElementById('createName')?.value || '').trim();
    var email = (document.getElementById('createEmail')?.value || '').trim();
    var pass = document.getElementById('createPassword')?.value || '';
    
    var rolesEl = document.getElementById('createRoles');
    var selectedRoles = rolesEl ? Array.from(rolesEl.selectedOptions).map(opt => opt.value) : [];

    if (!name || !email || !pass) { showError('createError', 'Nama, email, dan password wajib diisi.'); return; }
    if (selectedRoles.length === 0) { showError('createError', 'Pilih minimal satu role.'); return; }

    hideError('createError');
    setLoading('createSubmitBtn', 'createSpinner', true);
    api('POST', ROUTES.store, {
        name: name, email: email, password: pass,
        is_active: document.getElementById('createIsActive')?.value === '1',
        email_verified: false,
        roles: selectedRoles
    }).then(function(res){
        setLoading('createSubmitBtn', 'createSpinner', false);
        if (res.success) { AppModal.close('createModal'); showToast('success', res.message); setTimeout(function(){ location.reload(); }, 800); }
        else { showError('createError', res.message || 'Gagal membuat pengguna.'); }
    }).catch(function(){ setLoading('createSubmitBtn', 'createSpinner', false); showError('createError', 'Terjadi kesalahan server.'); });
};

// ── EDIT ──────────────────────────────────────────────────────────────────
window.openEditModal = function (user) {
    document.getElementById('editUserId').value  = user.id;
    document.getElementById('editName').value    = user.name;
    document.getElementById('editEmail').value   = user.email;
    document.getElementById('editPassword').value = '';
    document.getElementById('editIsActive').value = user.is_active ? '1' : '0';
    
    // Sync Alpine (dengan sedikit delay agar Alpine siap)
    if (user.roles) {
        setTimeout(function() {
            var slugs = Array.isArray(user.roles) ? user.roles.map(r => r.slug) : [];
            window.dispatchEvent(new CustomEvent('set-editRoles-roles', { detail: slugs }));
        }, 50);
    }

    var emailVerifiedContainer = document.getElementById('editEmailVerifiedContainer');
    if (user.email_verified_at) {
        emailVerifiedContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Terverifikasi</span>';
    } else {
        emailVerifiedContainer.innerHTML = '<div class="flex items-center gap-3"><span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20"><svg class="w-3.5 h-3.5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Belum</span><button type="button" onclick="sendVerificationEmailFromEdit(this)" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 border border-indigo-200 dark:border-indigo-500/20 transition-all rounded-md flex items-center gap-1"><svg class="w-3.5 h-3.5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> Kirim Verifikasi</button></div>';
    }

    document.getElementById('editModalSub').textContent = '#' + String(user.id).padStart(4,'0') + ' · ' + user.email;
    hideError('editError');
    AppModal.open('editModal');
};

window.submitEdit = function () {
    var id    = document.getElementById('editUserId')?.value;
    var name  = (document.getElementById('editName')?.value || '').trim();
    var email = (document.getElementById('editEmail')?.value || '').trim();
    
    var rolesEl = document.getElementById('editRoles');
    var selectedRoles = rolesEl ? Array.from(rolesEl.selectedOptions).map(opt => opt.value) : [];

    if (!name || !email) { showError('editError', 'Nama dan email wajib diisi.'); return; }
    if (selectedRoles.length === 0) { showError('editError', 'Pilih minimal satu role.'); return; }

    hideError('editError');
    setLoading('editSubmitBtn', 'editSpinner', true);
    api('PUT', ROUTES.update(id), {
        name: name, email: email,
        password: document.getElementById('editPassword')?.value || null,
        is_active: document.getElementById('editIsActive')?.value === '1',
        roles: selectedRoles
    }).then(function(res){
        setLoading('editSubmitBtn', 'editSpinner', false);
        if (res.success) { AppModal.close('editModal'); showToast('success', res.message); setTimeout(function(){ location.reload(); }, 800); }
        else { showError('editError', res.message || 'Gagal menyimpan perubahan.'); }
    }).catch(function(){ setLoading('editSubmitBtn', 'editSpinner', false); showError('editError', 'Terjadi kesalahan server.'); });
};

window.sendResetPasswordFromEdit = function (btn) {
    var id = document.getElementById('editUserId')?.value;
    if (!id) return;
    
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Mengirim...';

    api('POST', ROUTES.resetPwd(id)).then(function(res){
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        showToast(res.success ? 'success' : 'error', res.message);
    }).catch(function(){
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        showToast('error', 'Terjadi kesalahan sistem.');
    });
};

// ── DETAIL ────────────────────────────────────────────────────────────────
window.openDetailModal = function (user, isBlocked, blockCount, blockReason) {
    window.activeDetailUser = user;

    var statusHtml = '';
    if (isBlocked) {
        statusHtml = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">⊘ BLOCKED</span>';
    } else if (user.is_active) {
        statusHtml = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">● ACTIVE</span>';
    } else {
        statusHtml = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">○ INACTIVE</span>';
    }

    var mfaHtml = user.mfa_enabled 
        ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">MFA: ' + (user.mfa_type || 'Active').toUpperCase() + '</span>'
        : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700">MFA DISABLED</span>';

    var rolesHtml = (user.roles || []).map(r => 
        '<span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-[10px] font-medium">' + r.name + '</span>'
    ).join(' ') || '<span class="text-xs text-slate-400 italic">No Role Assigned</span>';

    var btnActionHtml = isBlocked 
        ? '<button onclick="AppPopup.close(); confirmUnblock(' + user.id + ', \'' + user.name.replace(/'/g, "\\'") + '\')" class="flex-1 py-2.5 px-4 text-[13px] font-bold bg-emerald-50 dark:bg-emerald-500/5 hover:bg-emerald-100 dark:hover:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 transition-all rounded-xl">Unblokir User</button>'
        : '<button onclick="AppPopup.close(); openBlockModal(' + user.id + ', \'' + user.name.replace(/'/g, "\\'") + '\')" class="flex-1 py-2.5 px-4 text-[13px] font-bold bg-red-50 dark:bg-red-500/5 hover:bg-red-100 dark:hover:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 transition-all rounded-xl">Blokir User</button>';

    var html = `
        <div class="text-left space-y-4 mt-6 border-t border-gray-100 dark:border-white/[0.08] pt-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-2xl font-bold text-white rounded-2xl shadow-inner flex-shrink-0">${user.name.charAt(0).toUpperCase()}</div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <p class="font-bold text-gray-900 dark:text-white text-[15px] leading-tight">${user.name}</p>
                        ${statusHtml}
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-slate-400 font-mono mt-0.5">${user.email}</p>
                    <div class="flex flex-wrap gap-1 mt-2">${rolesHtml}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 mt-2">
                <div class="bg-gray-50/80 dark:bg-white/[0.03] rounded-xl p-3 border border-gray-100 dark:border-white/[0.05]">
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 font-medium tracking-wide mb-1 flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg> User ID
                    </p>
                    <p class="text-xs font-bold text-gray-700 dark:text-slate-200 font-mono">#${String(user.id).padStart(4,'0')}</p>
                </div>
                <div class="bg-gray-50/80 dark:bg-white/[0.03] rounded-xl p-3 border border-gray-100 dark:border-white/[0.05]">
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 font-medium tracking-wide mb-1 flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> MFA Security
                    </p>
                    ${mfaHtml}
                </div>
                <div class="col-span-2 bg-gray-50/80 dark:bg-white/[0.03] rounded-xl p-3 border border-gray-100 dark:border-white/[0.05] flex justify-between items-center">
                    <div>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500 font-medium tracking-wide mb-1 flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Terdaftar
                        </p>
                        <p class="text-xs font-bold text-gray-700 dark:text-slate-200">${user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '—'}</p>
                    </div>
                    <div class="text-right">
                         <p class="text-[10px] text-gray-400 dark:text-slate-500 font-medium tracking-wide mb-1 flex items-center justify-end gap-1.5">
                            Akses Terakhir <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </p>
                        <p class="text-xs font-bold text-gray-700 dark:text-slate-200">${user.last_login_at ? new Date(user.last_login_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : 'Belum pernah login'}</p>
                        <p class="text-[9px] text-gray-400 font-mono mt-0.5">${user.last_login_ip || 'No Record'}</p>
                    </div>
                </div>
            </div>

            ${blockCount > 0 ? `
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl px-4 py-3 shadow-sm">
                <p class="text-[10px] text-red-600 dark:text-red-400 font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Riwayat Blokir (${blockCount}×)
                </p>
                <p class="text-[11px] text-gray-600 dark:text-slate-300 mb-1">Pengguna ini pernah melanggar kebijakan sebelumnya.</p>
                ${blockReason ? `<p class="text-[10px] text-gray-500 dark:text-slate-400 font-mono bg-white/50 dark:bg-black/20 px-2 py-1 rounded inline-block">Alasan: ${blockReason}</p>` : ''}
            </div>
            ` : ''}

            <div class="flex items-center gap-2 pt-2">
                <button onclick="AppPopup.close(); AppModal.open('editModal'); openEditModal(window.activeDetailUser)" class="flex-1 py-2.5 px-4 text-[13px] font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition-all rounded-xl active:scale-[0.98] outline-none">Edit User</button>
                ${btnActionHtml}
            </div>
        </div>
    `;

    AppPopup.show({
        type: 'custom',
        title: 'Detail Pengguna',
        description: 'Informasi lengkap terkait aktivitas dan status akun pengguna saat ini.',
        icon: '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        showButton: false, // Hidden, custom actions injected in template!
    });

    const descEl = document.getElementById('popup-desc');
    if(descEl) {
        descEl.innerHTML = html;
        descEl.classList.remove('hidden', 'mb-6');
    }
};

// ── BLOCK ─────────────────────────────────────────────────────────────────
window.openBlockModal = function (userId, userName) {
    document.getElementById('blockUserId').value = userId;
    document.getElementById('blockUserName').value = userName;
    document.getElementById('blockModalSub').textContent = userName;
    document.getElementById('blockReason').value = '';
    document.getElementById('blockUntil').value = '';
    AppModal.open('blockModal');
};

window.submitBlock = function () {
    var id     = document.getElementById('blockUserId')?.value;
    var reason = document.getElementById('blockReason')?.value;
    var until  = document.getElementById('blockUntil')?.value;
    if (!reason) { showToast('error', 'Pilih alasan blokir.'); return; }
    api('POST', ROUTES.block(id), { reason: reason, blocked_until: until || null }).then(function(res){
        AppModal.close('blockModal');
        showToast(res.success ? 'info' : 'error', res.message);
        if (res.success) setTimeout(function(){ location.reload(); }, 800);
    });
};

window.confirmUnblock = function (userId, userName) {
    AppPopup.warning({
        title: 'Unblokir Pengguna?',
        description: 'Apakah Anda yakin ingin membuka blokir untuk ' + userName + '?',
        confirmText: 'Ya, Unblokir',
        cancelText: 'Batal',
        onConfirm: function() {
            api('POST', ROUTES.unblock(userId)).then(function(res){
                showToast(res.success ? 'success' : 'error', res.message);
                if (res.success) setTimeout(function(){ location.reload(); }, 800);
            });
        }
    });
};

// ── DELETE ────────────────────────────────────────────────────────────────
window.confirmDelete = function (userId, userName) {
    AppPopup.confirm({
        title: 'Hapus Pengguna?',
        description: 'Akun ' + userName + ' akan dihapus. Tindakan ini tidak dapat dibatalkan.',
        confirmText: 'Ya, Hapus',
        onConfirm: function() {
            api('DELETE', ROUTES.destroy(userId)).then(function(res){
                showToast(res.success ? 'success' : 'error', res.message);
                if (res.success) setTimeout(function(){ location.reload(); }, 800);
            });
        }
    });
};

// ── RESET PASSWORD ────────────────────────────────────────────────────────
window.sendResetPassword = function (userId, email, btn) {
    AppPopup.info({
        title: 'Reset Password?',
        description: 'Kirim link reset password ke ' + email + '?',
        confirmText: 'Kirim Link',
        cancelText: 'Batal',
        onConfirm: function() {
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';

            api('POST', ROUTES.resetPwd(userId)).then(function(res){
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showToast(res.success ? 'success' : 'error', res.message);
            }).catch(function(){
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showToast('error', 'Terjadi kesalahan sistem.');
            });
        }
    });
};
