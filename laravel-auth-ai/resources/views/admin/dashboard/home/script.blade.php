<script>
(function () {

    // ── isDark: samakan dengan cara layout ──────────────────────────────────
    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    // ── Palet warna chart ────────────────────────────────────────────────────
    var C = {
        emerald : { s: '#10b981', a: function(v){ return 'rgba(16,185,129,'+v+')';  } },
        amber   : { s: '#f59e0b', a: function(v){ return 'rgba(245,158,11,'+v+')';  } },
        red     : { s: '#ef4444', a: function(v){ return 'rgba(239,68,68,'+v+')';   } },
        slate   : { s: '#94a3b8', a: function(v){ return 'rgba(148,163,184,'+v+')'; } },
        sky     : { s: '#38bdf8', a: function(v){ return 'rgba(56,189,248,'+v+')';  } },
        violet  : { s: '#8b5cf6', a: function(v){ return 'rgba(139,92,246,'+v+')';  } },
    };

    function gridColor()    { return isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';       }
    function tickColor()    { return isDark() ? '#475569' : '#94a3b8';                                 }
    function tooltipBg()    { return isDark() ? '#1e293b' : '#ffffff';                                 }
    function tooltipFg()    { return isDark() ? '#e2e8f0' : '#1e293b';                                 }
    function borderColor()  { return isDark() ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';        }

    // ── Data dari server ─────────────────────────────────────────────────────
    var chartLabels    = @json($chartLabels ?? []);
    var chartSuccess   = @json($chartSuccess ?? []);
    var chartOtp       = @json($chartOtp ?? []);
    var chartBlocked   = @json($chartBlocked ?? []);
    var chartFailed    = @json($chartFailed ?? []);
    var riskAvg        = @json($riskAvg ?? []);
    var riskMax        = @json($riskMax ?? []);
    const decisionCounts = @json($decisionCounts);

    // ── Data Hari Ini (per jam) ───────────────────────────────────────────────
    // Label jam: 00, 01, ..., 23
    var hourLabels = Array.from({length: 24}, function(_, i){ return (i < 10 ? '0' : '') + i; });

    var todaySuccessHourly = @json($todaySuccessHourly ?? array_fill(0, 24, 0));
    var todayOtpHourly     = @json($todayOtpHourly     ?? array_fill(0, 24, 0));
    var todayFailedHourly  = @json($todayFailedHourly  ?? array_fill(0, 24, 0));
    var todayBlockedHourly = @json($todayBlockedHourly ?? array_fill(0, 24, 0));

    // ── Registry chart instances ─────────────────────────────────────────────
    var charts = {};

    // Font ikut DM Mono yang sudah load di layout
    Chart.defaults.font.family = "'DM Mono', 'ui-monospace', monospace";
    Chart.defaults.font.size   = 10;

    function baseOpts() {
        return {
            responsive          : true,
            maintainAspectRatio : false,
            interaction         : { mode: 'index', intersect: false },
            plugins: {
                legend : { display: false },
                tooltip: {
                    backgroundColor : tooltipBg(),
                    titleColor      : tooltipFg(),
                    bodyColor       : tooltipFg(),
                    borderColor     : borderColor(),
                    borderWidth     : 1,
                    padding         : 10,
                    cornerRadius    : 8,
                },
            },
            scales: {
                x: {
                    grid : { color: gridColor(), drawBorder: false },
                    ticks: { color: tickColor(), maxRotation: 0, maxTicksLimit: 8 },
                },
                y: {
                    grid        : { color: gridColor(), drawBorder: false },
                    ticks       : { color: tickColor() },
                    beginAtZero : true,
                },
            },
        };
    }

    // ── Opsi minimal untuk chart hari ini (compact, tanpa axis) ─────────────
    function miniBarOpts(tooltipLabel) {
        return {
            responsive          : true,
            maintainAspectRatio : false,
            interaction         : { mode: 'index', intersect: false },
            plugins: {
                legend : { display: false },
                tooltip: {
                    backgroundColor : tooltipBg(),
                    titleColor      : tooltipFg(),
                    bodyColor       : tooltipFg(),
                    borderColor     : borderColor(),
                    borderWidth     : 1,
                    padding         : 8,
                    cornerRadius    : 6,
                    callbacks       : {
                        title : function(ctx){ return 'Jam ' + ctx[0].label + ':00'; },
                        label : function(c){ return ' ' + (tooltipLabel || c.dataset.label) + ': ' + c.parsed.y; },
                    },
                },
            },
            scales: {
                x: {
                    grid  : { display: false },
                    border: { display: false },
                    ticks : {
                        color        : tickColor(),
                        maxRotation  : 0,
                        maxTicksLimit: 6,
                        font         : { size: 9 },
                    },
                },
                y: {
                    display    : false,
                    beginAtZero: true,
                },
            },
        };
    }

    function kill(id) { if (charts[id]) { charts[id].destroy(); delete charts[id]; } }

    // ── Build: Login Activity ────────────────────────────────────────────────
    function buildLogin() {
        kill('login');
        var el = document.getElementById('loginActivityChart');
        if (!el) return;
        function ds(lbl, data, col, fill) {
            return { label: lbl, data: data, borderColor: col.s, backgroundColor: fill ? col.a(0.08) : 'transparent', borderWidth: 2, pointRadius: 2.5, pointHoverRadius: 5, pointBackgroundColor: col.s, tension: 0.4, fill: !!fill };
        }
        var opts = baseOpts();
        opts.plugins.tooltip.callbacks = { label: function(c){ return ' '+c.dataset.label+': '+c.parsed.y.toLocaleString(); } };
        charts['login'] = new Chart(el, {
            type: 'line',
            data: { labels: chartLabels, datasets: [ds('Sukses',chartSuccess,C.emerald,true),ds('OTP',chartOtp,C.amber,false),ds('Blocked',chartBlocked,C.red,false),ds('Gagal',chartFailed,C.slate,false)] },
            options: opts,
        });
    }

    // ── Build: Risk Score ────────────────────────────────────────────────────
    function buildRisk() {
        kill('risk');
        var el = document.getElementById('riskScoreChart');
        if (!el) return;
        var opts = baseOpts();
        opts.scales.y.max = 100;
        opts.scales.y.ticks.callback = function(v){ return v+'%'; };
        charts['risk'] = new Chart(el, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [
                    { label:'Avg Risk', data:riskAvg,  borderColor:C.violet.s, backgroundColor:C.violet.a(0.1), borderWidth:2,   pointRadius:2, pointHoverRadius:4, tension:0.4, fill:true  },
                    { label:'Max Risk', data:riskMax,  borderColor:C.red.s,    backgroundColor:'transparent',   borderWidth:1.5, pointRadius:2, pointHoverRadius:4, tension:0.4, fill:false, borderDash:[4,3] },
                ],
            },
            options: opts,
        });
    }

    // ── Build: Donut ─────────────────────────────────────────────────────────
    function buildDonut() {
        kill('donut');
        var el = document.getElementById('decisionDonut');
        if (!el) return;
        charts['donut'] = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['ALLOW','OTP','BLOCK','FALLBACK','PENDING'],
                datasets: [{ data:[decisionCounts.ALLOW||0,decisionCounts.OTP||0,decisionCounts.BLOCK||0,decisionCounts.FALLBACK||0,decisionCounts.PENDING||0], backgroundColor:[C.emerald.s,C.amber.s,C.red.s,C.slate.s,C.sky.s], borderWidth:0, hoverOffset:4 }],
            },
            options: {
                responsive:true, maintainAspectRatio:false, cutout:'70%',
                plugins: {
                    legend:{display:false},
                    tooltip:{ backgroundColor:tooltipBg(), titleColor:tooltipFg(), bodyColor:tooltipFg(), borderColor:borderColor(), borderWidth:1, padding:8, cornerRadius:6, callbacks:{ label:function(c){ return ' '+c.label+': '+c.parsed.toLocaleString(); } } },
                },
            },
        });
    }

    // ── Build: Today Success ─────────────────────────────────────────────────
    function buildTodaySuccess() {
        kill('todaySuccess');
        var el = document.getElementById('todaySuccessChart');
        if (!el) return;
        var opts = miniBarOpts('Login Sukses');
        charts['todaySuccess'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label          : 'Login Sukses',
                    data           : todaySuccessHourly,
                    backgroundColor: function(ctx) {
                        var val = ctx.parsed ? ctx.parsed.y : 0;
                        var max = Math.max.apply(null, todaySuccessHourly) || 1;
                        var alpha = 0.25 + (val / max) * 0.75;
                        return 'rgba(16,185,129,' + alpha + ')';
                    },
                    borderColor    : C.emerald.s,
                    borderWidth    : 0,
                    borderRadius   : 3,
                    borderSkipped  : false,
                }],
            },
            options: opts,
        });
    }

    // ── Build: Today OTP ─────────────────────────────────────────────────────
    function buildTodayOtp() {
        kill('todayOtp');
        var el = document.getElementById('todayOtpChart');
        if (!el) return;
        var opts = miniBarOpts('Login OTP');
        charts['todayOtp'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label          : 'Login OTP',
                    data           : todayOtpHourly,
                    backgroundColor: function(ctx) {
                        var val = ctx.parsed ? ctx.parsed.y : 0;
                        var max = Math.max.apply(null, todayOtpHourly) || 1;
                        var alpha = 0.25 + (val / max) * 0.75;
                        return 'rgba(245,158,11,' + alpha + ')';
                    },
                    borderColor    : C.amber.s,
                    borderWidth    : 0,
                    borderRadius   : 3,
                    borderSkipped  : false,
                }],
            },
            options: opts,
        });
    }

    // ── Build: Today Threat (Gagal + Block) ──────────────────────────────────
    function buildTodayThreat() {
        kill('todayThreat');
        var el = document.getElementById('todayThreatChart');
        if (!el) return;
        var opts = miniBarOpts(null);
        // Stacked bar: Gagal (slate) di bawah, Blocked (red) di atas
        opts.scales.x.stacked = true;
        opts.scales.y.stacked = true;
        opts.plugins.tooltip.callbacks = {
            title : function(ctx){ return 'Jam ' + ctx[0].label + ':00'; },
            label : function(c){ return ' ' + c.dataset.label + ': ' + c.parsed.y; },
        };
        charts['todayThreat'] = new Chart(el, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [
                    {
                        label          : 'Gagal',
                        data           : todayFailedHourly,
                        backgroundColor: C.slate.a(0.55),
                        borderColor    : 'transparent',
                        borderWidth    : 0,
                        borderRadius   : { topLeft: 0, topRight: 0, bottomLeft: 3, bottomRight: 3 },
                        borderSkipped  : false,
                        stack          : 'threat',
                    },
                    {
                        label          : 'Blocked',
                        data           : todayBlockedHourly,
                        backgroundColor: C.red.a(0.65),
                        borderColor    : 'transparent',
                        borderWidth    : 0,
                        borderRadius   : { topLeft: 3, topRight: 3, bottomLeft: 0, bottomRight: 0 },
                        borderSkipped  : false,
                        stack          : 'threat',
                    },
                ],
            },
            options: opts,
        });
    }

    function buildAll() {
        buildLogin();
        buildRisk();
        buildDonut();
        buildTodaySuccess();
        buildTodayOtp();
        buildTodayThreat();
    }

    // ── GLOBAL: rebuildCharts dipanggil toggleDark() di layout ───────────────
    window.rebuildCharts = function () { buildAll(); };

    // ── GLOBAL: refreshDashboard ─────────────────────────────────────────────
    window.refreshDashboard = function () {
        var icon = document.getElementById('refreshIcon');
        if (icon) {
            icon.style.transition = 'transform 0.4s ease';
            icon.style.transform  = 'rotate(360deg)';
            setTimeout(function(){ icon.style.transform = ''; }, 400);
        }
        setTimeout(function(){ window.location.reload(); }, 350);
    };

    // ── GLOBAL: setPeriod — plain JS, ganti URL lalu reload ──────────────────
    window.setPeriod = function (period) {
        document.querySelectorAll('.period-btn').forEach(function (btn) {
            var active = btn.dataset.period === period;
            btn.classList.remove('bg-white','shadow-sm','text-slate-800','text-slate-500');
            if (active) {
                btn.classList.add('bg-white', 'shadow-sm', 'text-slate-800');
            } else {
                btn.classList.add('text-slate-500');
            }
        });
        var url = new URL(window.location.href);
        url.searchParams.set('period', period);
        window.location.href = url.toString();
    };

    // ── Init setelah DOM siap ─────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildAll);
    } else {
        buildAll();
    }

}());
</script>