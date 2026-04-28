{{-- MODALS FOR AUTH LOGS --}}
@include('security::log.modals.details')
@include('security::log.modals.bulk_delete')

<script>
    const LOG_API_BASE = '{{ url("admin/security/logs") }}';
    const LOG_CSRF = '{{ csrf_token() }}';

    /**
     * Simple Tab Switching (No AJAX)
     */
    function switchLogTab(tab) {
        const panelAuth = document.getElementById('panel-auth');
        const panelAudit = document.getElementById('panel-audit');
        const btnAuth = document.getElementById('tab-btn-auth');
        const btnAudit = document.getElementById('tab-btn-audit');

        const activeClasses = ['bg-white', 'dark:bg-slate-700', 'text-slate-800', 'dark:text-white', 'shadow-sm', 'border', 'border-slate-200', 'dark:border-slate-600'];
        const inactiveClasses = ['text-slate-400', 'hover:text-slate-600', 'dark:hover:text-slate-300'];

        if (tab === 'auth') {
            panelAuth.classList.remove('hidden');
            panelAudit.classList.add('hidden');
            btnAuth.classList.add(...activeClasses); btnAuth.classList.remove(...inactiveClasses);
            btnAudit.classList.add(...inactiveClasses); btnAudit.classList.remove(...activeClasses);
        } else {
            panelAudit.classList.remove('hidden');
            panelAuth.classList.add('hidden');
            btnAudit.classList.add(...activeClasses); btnAudit.classList.remove(...inactiveClasses);
            btnAuth.classList.add(...inactiveClasses); btnAuth.classList.remove(...activeClasses);
        }

        // Update URL without refresh
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({ tab }, '', url.href);
    }

    /**
     * Auth Log Details
     */
    async function viewLogDetails(id) {
        try {
            const resp = await fetch(`${LOG_API_BASE}/${id}/details`);
            const res = await resp.json();
            
            if (res.success) {
                const data = res.data;
                document.getElementById('det-id').textContent = data.id;
                document.getElementById('det-email').textContent = data.email;
                document.getElementById('det-ip').textContent = data.ip;
                document.getElementById('det-time').textContent = data.occurred_at;
                document.getElementById('det-country').textContent = data.country_code || 'Internal / Private IP';
                document.getElementById('det-browser').textContent = `${data.browser} ${data.browser_version}`;
                document.getElementById('det-platform').textContent = `${data.platform} ${data.platform_version} (${data.device})`;
                document.getElementById('det-ua-full').textContent = data.ua;
                document.getElementById('det-raw').textContent = JSON.stringify(data.raw, null, 2);

                const score = data.risk_score || 0;
                const decision = data.decision;
                const riskLabelEl = document.getElementById('det-risk-label');
                const riskDescEl = document.getElementById('det-risk-desc');
                const sectionEl = document.getElementById('risk-analysis-section');
                const circleEl = document.getElementById('det-risk-circle');
                const scoreEl = document.getElementById('det-risk-score');

                let colorClass = 'emerald', label = 'PASSED', desc = 'Aman.';
                if (decision === 'MFA') { colorClass = 'indigo'; label = 'MFA ENFORCED'; desc = 'Wajib verifikasi.'; }
                else if (decision === 'OTP') { colorClass = 'amber'; label = 'AI CHALLENGE'; desc = 'Deteksi anomali.'; }
                else if (decision === 'BLOCK') { colorClass = 'red'; label = 'AI BLOCKED'; desc = 'Akses ditolak.'; }

                const colorMap = {
                    emerald: { border: 'border-emerald-100', text: 'text-emerald-600', circle: 'text-emerald-500', label: 'bg-emerald-100 text-emerald-600' },
                    amber: { border: 'border-amber-100', text: 'text-amber-600', circle: 'text-amber-500', label: 'bg-amber-100 text-amber-600' },
                    red: { border: 'border-red-100', text: 'text-red-600', circle: 'text-red-500', label: 'bg-red-100 text-red-600' },
                    indigo: { border: 'border-indigo-100', text: 'text-indigo-600', circle: 'text-indigo-500', label: 'bg-indigo-100 text-indigo-600' }
                };

                const currentMap = colorMap[colorClass];
                sectionEl.className = `relative overflow-hidden p-6 rounded border transition-all duration-500 ${currentMap.border}`;
                riskLabelEl.className = `px-2 py-0.5 rounded text-[9px] font-black uppercase ${currentMap.label}`;
                riskLabelEl.textContent = label; riskDescEl.textContent = desc;
                scoreEl.textContent = decision === 'MFA' ? '--' : score + '%';
                scoreEl.className = `absolute text-sm font-black tabular-nums ${currentMap.text}`;
                circleEl.className = `transition-all duration-1000 ease-out ${currentMap.circle}`;
                const offset = 175.92 - (175.92 * (decision === 'MFA' ? 100 : score) / 100);
                circleEl.style.strokeDashoffset = offset;

                const statusStyles = { 'success': 'bg-emerald-50 text-emerald-600 border-emerald-100', 'failed': 'bg-red-50 text-red-600 border-red-100', 'blocked': 'bg-slate-900 text-white border-slate-800', 'otp_required': 'bg-amber-50 text-amber-600 border-amber-100' };
                const sStyle = statusStyles[data.status] || 'bg-slate-50 text-slate-600 border-slate-100';
                document.getElementById('det-status-badge').innerHTML = `<span class="px-3 py-1 rounded text-[10px] font-bold border uppercase tracking-wider ${sStyle}">${data.status.replace('_', ' ')}</span>`;

                const flagsCont = document.getElementById('det-flags');
                flagsCont.innerHTML = '';
                if (data.reason_flags && data.reason_flags.length > 0) {
                    data.reason_flags.forEach(flag => {
                        const span = document.createElement('span'); span.className = 'px-2 py-0.5 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[9px] text-slate-600 dark:text-slate-400 font-mono';
                        span.textContent = flag; flagsCont.appendChild(span);
                    });
                } else { flagsCont.innerHTML = '<span class="text-[10px] text-slate-400 italic font-medium">No flags</span>'; }

                AppModal.open('logDetailsModal');
            }
        } catch (err) { console.error(err); showToast('error', 'Gagal memuat detail.'); }
    }

    window.openBulkDeleteLogsModal = function() { AppModal.open('bulkDeleteLogsModal'); }
    window.submitBulkDeleteLogs = function() {
        const start = document.getElementById('bulk-log-start-date').value;
        const end = document.getElementById('bulk-log-end-date').value;
        if (!start || !end) { showToast('error', 'Tentukan rentang waktu.'); return; }
        const btn = document.getElementById('btn-submit-bulk-delete-logs');
        const spinner = document.getElementById('bulk-delete-logs-spinner');
        btn.disabled = true; spinner.classList.remove('hidden');
        fetch(`${LOG_API_BASE}/bulk-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': LOG_CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ start_date: start, end_date: end })
        })
        .then(r => r.json()).then(res => {
            if (res.success) { showToast('success', res.message); AppModal.close('bulkDeleteLogsModal'); setTimeout(() => location.reload(), 800); }
            else { showToast('error', res.message); btn.disabled = false; spinner.classList.add('hidden'); }
        }).catch(() => { showToast('error', 'Kesalahan server.'); btn.disabled = false; spinner.classList.add('hidden'); });
    }

    function showToast(type, message) {
        if (window.AppPopup) {
            if (type === 'success') AppPopup.success({ description: message });
            else AppPopup.error({ description: message });
        } else { alert(message); }
    }
</script>
