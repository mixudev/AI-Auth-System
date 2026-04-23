// ── Selection ─────────────────────────────────────────────────────────────
var selectedIds = [];

window.updateSelection = function () {
    selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(function(c){ return parseInt(c.value); });
    var bar   = document.getElementById('bulkBar');
    var count = document.getElementById('bulkCount');
    var all   = document.getElementById('selectAll');
    var total = document.querySelectorAll('.row-checkbox').length;
    if (selectedIds.length > 0) {
        if(bar) bar.classList.replace('hidden', 'flex');
        if(count) count.textContent = selectedIds.length + ' pengguna dipilih';
    } else {
        if(bar) bar.classList.replace('flex', 'hidden');
    }
    if(all) {
        all.indeterminate = selectedIds.length > 0 && selectedIds.length < total;
        all.checked = selectedIds.length > 0 && selectedIds.length === total;
    }
};

window.toggleSelectAll = function (cb) {
    document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked = cb.checked; });
    updateSelection();
};

window.clearSelection = function () {
    document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked = false; });
    var all = document.getElementById('selectAll');
    if(all) all.checked = false;
    selectedIds = [];
    var bar = document.getElementById('bulkBar');
    if(bar) bar.classList.replace('flex', 'hidden');
};

// ── BULK ──────────────────────────────────────────────────────────────────
window.openBulkBlockModal = function () {
    if (selectedIds.length === 0) return;
    var countEl = document.getElementById('bulkBlockCount');
    if(countEl) countEl.textContent = selectedIds.length + ' pengguna akan diblokir';
    var reasonEl = document.getElementById('bulkBlockReason');
    if(reasonEl) reasonEl.value = '';
    AppModal.open('bulkBlockModal');
};

window.submitBulkBlock = function () {
    var reasonEl = document.getElementById('bulkBlockReason');
    var reason = reasonEl ? reasonEl.value : '';
    if (!reason) { showToast('error', 'Pilih alasan blokir.'); return; }
    api('POST', ROUTES.bulk, { action: 'block', user_ids: selectedIds, reason: reason }).then(function(res){
        AppModal.close('bulkBlockModal');
        showToast(res.success ? 'info' : 'error', res.message);
        if (res.success) setTimeout(function(){ location.reload(); }, 800);
    });
};

window.bulkAction = function (action) {
    if (selectedIds.length === 0) return;
    var labels = { unblock: 'unblokir', delete: 'hapus' };
    var actionLabel = labels[action] || action;
    var type = action === 'delete' ? 'confirm' : 'warning';
    
    AppPopup.show({
        type: type,
        title: 'Aksi Massal: ' + actionLabel.toUpperCase() + '?',
        description: 'Apakah Anda yakin ingin menjalankan aksi ' + actionLabel + ' pada ' + selectedIds.length + ' pengguna yang dipilih?',
        confirmText: 'Ya, Jalankan',
        cancelText: 'Batal',
        onConfirm: function() {
            api('POST', ROUTES.bulk, { action: action, user_ids: selectedIds }).then(function(res){
                showToast(res.success ? 'success' : 'error', res.message);
                if (res.success) setTimeout(function(){ location.reload(); }, 800);
            });
        }
    });
};
