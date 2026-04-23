// ─── Delegated Actions ───────────────────────────────────────────────────────
document.addEventListener('click', e => {
    const trigger = e.target.closest('[data-profile-action]');
    if (!trigger) return;

    const action = trigger.dataset.profileAction;
    if (action === 'setup-mfa') { e.preventDefault(); window.startMfaSetup(); }
    if (action === 'disable-mfa') { e.preventDefault(); window.openDisableMfaModal(); }
    if (action === 'reset-password') { e.preventDefault(); window.confirmResetSandi(); }
});
