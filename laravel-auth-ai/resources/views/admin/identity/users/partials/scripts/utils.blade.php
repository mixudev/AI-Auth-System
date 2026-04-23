// ── Modal helpers ─────────────────────────────────────────────────────────
function setLoading(btnId, spinnerId, loading) {
    var btn = document.getElementById(btnId);
    if(btn) btn.disabled = loading;
    var spin = document.getElementById(spinnerId);
    if(spin) spin.classList.toggle('hidden', !loading);
}

function showError(elId, msg) {
    var el = document.getElementById(elId);
    var msgEl = document.getElementById(elId + 'Msg');
    if(msgEl) msgEl.textContent = msg;
    if(el) { el.classList.remove('hidden'); el.classList.add('flex'); }
}

function hideError(elId) {
    var el = document.getElementById(elId);
    if(el) { el.classList.add('hidden'); el.classList.remove('flex'); }
}

// ── Password toggle ───────────────────────────────────────────────────────
window.togglePassword = function (inputId, btn) {
    var inp = document.getElementById(inputId);
    if(inp) inp.type = inp.type === 'password' ? 'text' : 'password';
};
