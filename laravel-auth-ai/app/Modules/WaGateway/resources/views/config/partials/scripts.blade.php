<script>
(function() {
    const CSRF = '{{ csrf_token() }}';
    const ROUTES = {
        store: '{{ route("wa-gateway.config.store") }}',
        update: (id) => `{{ url("dashboard/wa-gateway") }}/${id}`,
        destroy: (id) => `{{ url("dashboard/wa-gateway") }}/${id}`,
        toggle: (id) => `{{ url("dashboard/wa-gateway") }}/${id}/toggle`,
        test: (id) => `{{ url("dashboard/wa-gateway") }}/${id}/test`,
        logsLatest: '{{ route("wa-gateway.config.logs.latest") }}',
        
        tplStore: '{{ route("wa-gateway.templates.store") }}',
        tplUpdate: (id) => `{{ url("dashboard/wa-gateway/templates") }}/${id}`,
        tplDestroy: (id) => `{{ url("dashboard/wa-gateway/templates") }}/${id}`,
        systemConfigUpdate: '{{ route("wa-gateway.config.settings.update") }}',
    };

    // ─── LOG DETAIL HANDLER (Delegated)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-log-detail');
        if (btn) {
            const log = JSON.parse(btn.getAttribute('data-log'));
            openLogDetail(log);
        }
    });

    window.openLogDetail = function(log) {
        const responseData = (log && log.response_data) ? log.response_data : null;
        document.getElementById('logDetailStatus').textContent = (log.status || '-').toUpperCase();
        document.getElementById('logDetailGateway').textContent = log.gateway || '-';
        document.getElementById('logDetailTarget').textContent = log.target || '-';
        document.getElementById('logDetailSentAt').textContent = log.sent_at || '-';
        document.getElementById('logDetailMessage').textContent = log.message || '-';
        document.getElementById('logDetailResponseId').textContent = log.response_id || '-';
        document.getElementById('logDetailError').textContent = log.error_message || (responseData && responseData.reason ? responseData.reason : '-');
        document.getElementById('logDetailResponseData').textContent = responseData ? JSON.stringify(responseData, null, 2) : '-';
        AppModal.open('logDetailModal');
    };

    window.openInfoModal = function() {
        AppModal.open('infoModal');
    };

    // ─── GATEWAY JS
    window.testSystemConnection = function() {
        showToast('info', 'Mencoba tes koneksi jalur utama...');
        fetch(ROUTES.systemConfigUpdate + '/test', { 
            method: 'POST', 
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } 
        })
        .then(r => r.json())
        .then(res => showToast(res.success ? 'success' : 'error', res.message));
    };

    window.openCreateModal = function() {
        document.getElementById('createName').value = '';
        document.getElementById('createPurpose').value = 'security';
        document.getElementById('createToken').value = '';
        document.getElementById('createAlertNumber').value = '';
        AppModal.open('createModal');
    };

    window.submitCreate = function() {
        const name = document.getElementById('createName').value.trim();
        const purpose = document.getElementById('createPurpose').value;
        const token = document.getElementById('createToken').value.trim();
        const alert_phone_number = document.getElementById('createAlertNumber').value.trim();
        if(!name || !token || !alert_phone_number) return;
        
        fetch(ROUTES.store, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name, purpose, token, alert_phone_number })
        }).then(r => r.json()).then(res => {
            if(res.success) location.reload();
        });
    };

    window.openEditModal = function(config) {
        document.getElementById('editConfigId').value = config.id;
        document.getElementById('editName').value = config.name;
        document.getElementById('editPurpose').value = config.purpose;
        document.getElementById('editToken').value = '';
        document.getElementById('editAlertNumber').value = config.alert_phone_number;
        document.getElementById('editIsActive').checked = !!config.is_active;
        AppModal.open('editModal');
    };

    window.submitEdit = function() {
        const id = document.getElementById('editConfigId').value;
        const name = document.getElementById('editName').value;
        const purpose = document.getElementById('editPurpose').value;
        const token = document.getElementById('editToken').value;
        const alert_phone_number = document.getElementById('editAlertNumber').value;
        const is_active = document.getElementById('editIsActive').checked;
        
        fetch(ROUTES.update(id), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name, purpose, token, alert_phone_number, is_active })
        }).then(r => r.json()).then(res => {
            if(res.success) location.reload();
        });
    };

    window.toggleConfig = function(id, btn) {
        fetch(ROUTES.toggle(id), { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
        .then(r => r.json()).then(res => {
            if(res.success) {
                const indicator = btn.querySelector('span');
                if(res.is_active) {
                    btn.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-400');
                    btn.classList.add('bg-emerald-50', 'dark:bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400', 'ring-1', 'ring-emerald-500/20', 'shadow-sm');
                    indicator.classList.remove('bg-slate-300');
                    indicator.classList.add('bg-emerald-500', 'animate-pulse', 'shadow-[0_0_8px_rgba(16,185,129,0.6)]');
                    btn.lastChild.textContent = ' Online';
                } else {
                    btn.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-400');
                    btn.classList.remove('bg-emerald-50', 'dark:bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400', 'ring-1', 'ring-emerald-500/20', 'shadow-sm');
                    indicator.classList.add('bg-slate-300');
                    indicator.classList.remove('bg-emerald-500', 'animate-pulse', 'shadow-[0_0_8px_rgba(16,185,129,0.6)]');
                    btn.lastChild.textContent = ' Offline';
                }
            }
        });
    }

    window.testConnection = function(id) {
        showToast('info', 'Mencoba mengirim pesan...');
        fetch(ROUTES.test(id), { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
        .then(r => r.json()).then(res => showToast(res.success ? 'success' : 'error', res.message));
    };

    window.confirmDelete = function(id, name) {
        AppPopup.confirm({ title: 'Hapus?', onConfirm: () => {
            fetch(ROUTES.destroy(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(r => r.json()).then(res => location.reload());
        }});
    };

    // ─── TEMPLATE JS
    window.openTemplateModal = function() {
        document.getElementById('tplId').value = '';
        document.getElementById('tplName').value = '';
        document.getElementById('tplSlug').value = '';
        document.getElementById('tplPurpose').value = 'security';
        document.getElementById('tplContent').value = '';
        AppModal.open('templateModal');
    };

    window.openTemplateEditModal = function(tpl) {
        document.getElementById('tplId').value = tpl.id;
        document.getElementById('tplName').value = tpl.name;
        document.getElementById('tplSlug').value = tpl.slug;
        document.getElementById('tplPurpose').value = tpl.purpose;
        document.getElementById('tplContent').value = tpl.content;
        AppModal.open('templateModal');
    };

    window.submitTemplate = function() {
        const id = document.getElementById('tplId').value;
        const name = document.getElementById('tplName').value.trim();
        const slug = document.getElementById('tplSlug').value.trim();
        const purpose = document.getElementById('tplPurpose').value;
        const content = document.getElementById('tplContent').value.trim();

        if(!name || !content) return;

        const isEdit = !!id;
        const url = isEdit ? ROUTES.tplUpdate(id) : ROUTES.tplStore;
        const method = isEdit ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name, slug, purpose, content })
        })
        .then(async r => {
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || `Server error: ${r.status}`);
            return data;
        })
        .then(res => {
            showToast('success', res.message);
            setTimeout(() => location.reload(), 800);
        })
        .catch(err => {
            console.error(err);
            showToast('error', err.message);
        });
    };

    window.confirmDeleteTemplate = function(id, name) {
        AppPopup.confirm({
            title: 'Hapus Template?',
            description: `Template "${name}" akan dihapus.`,
            onConfirm: () => {
                fetch(ROUTES.tplDestroy(id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                }).then(() => location.reload());
            }
        });
    };

    // ─── SYSTEM CONFIG JS
    window.openSystemConfigModal = function() {
        AppModal.open('systemConfigModal');
    };

    window.submitSystemConfig = function() {
        const form = document.getElementById('systemConfigForm');
        const formData = new FormData(form);
        
        // Handle checkboxes (since they aren't included in FormData if unchecked)
        formData.set('guardrail[enabled]', document.getElementById('sysGuardEnabled').checked ? '1' : '0');
        formData.set('guardrail[allow_critical_in_quiet_hours]', document.getElementById('sysGuardAllowCritical').checked ? '1' : '0');

        const submitBtn = document.getElementById('sysConfigSubmitBtn');
        const spinner = document.getElementById('sysConfigSpinner');
        
        submitBtn.disabled = true;
        spinner.classList.remove('hidden');

        fetch(ROUTES.systemConfigUpdate, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': CSRF, 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async r => {
            const text = await r.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch(e) {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned invalid response. Check console for details.');
            }

            if (!r.ok) {
                throw new Error(data.message || `Error ${r.status}`);
            }
            return data;
        })
        .then(res => {
            showToast('success', res.message);
            AppModal.close('systemConfigModal');
            setTimeout(() => location.reload(), 1000);
        })
        .catch(err => {
            console.error(err);
            showToast('error', err.message);
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('hidden');
        });
    };

    // ─── LOG AUTO REFRESH
    setInterval(() => {
        fetch(ROUTES.logsLatest, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json()).then(logs => {
             // Real-time refresh logic can be expanded here if needed
        });
    }, 30000);

})();
</script>
