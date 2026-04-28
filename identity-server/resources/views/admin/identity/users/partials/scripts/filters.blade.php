// ── Filter form ───────────────────────────────────────────────────────────
var debounceTimer;
window.debounceSubmit = function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function(){ 
        var form = document.getElementById('filterForm');
        if(form) form.submit(); 
    }, 600);
};
window.setStatus = function (val) {
    var input = document.getElementById('statusInput');
    if(input) input.value = val;
    document.querySelectorAll('.status-btn').forEach(function(btn){
        var active = btn.dataset.status === val;
        btn.classList.toggle('bg-white', active);
        btn.classList.toggle('dark:bg-slate-700', active);
        btn.classList.toggle('text-slate-800', active);
        btn.classList.toggle('dark:text-white', active);
        btn.classList.toggle('text-slate-500', !active);
        btn.classList.toggle('dark:text-slate-400', !active);
    });
    var form = document.getElementById('filterForm');
    if(form) form.submit();
};
