<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Auth Monitor — DEV</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ── Reset & Variables ─────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:       #07090f;
  --s1:       #0b0e18;
  --s2:       #0f1320;
  --s3:       #141928;
  --border:   #1c2235;
  --border2:  #242c42;
  --text:     #c8d0e0;
  --text2:    #7a88a6;
  --text3:    #3d4a65;
  --cyan:     #38bdf8;
  --cyan-dim: rgba(56,189,248,.12);
  --green:    #4ade80;
  --green-dim:rgba(74,222,128,.10);
  --amber:    #fbbf24;
  --amber-dim:rgba(251,191,36,.10);
  --red:      #f87171;
  --red-dim:  rgba(248,113,113,.10);
  --violet:   #a78bfa;
  --mono:     'IBM Plex Mono', monospace;
  --sans:     'DM Sans', sans-serif;
  --r:        6px;
}

body {
  background: var(--bg);
  color: var(--text);
  font-family: var(--sans);
  min-height: 100vh;
  font-size: 13px;
  line-height: 1.5;
  overflow-x: hidden;
}

/* Subtle grid texture */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image:
    linear-gradient(rgba(56,189,248,.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(56,189,248,.025) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
  z-index: 0;
}

/* ── Header ───────────────────────────────────────────────────────────── */
.hdr {
  position: sticky; top: 0; z-index: 200;
  background: rgba(7,9,15,.92);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 28px; height: 52px;
}
.hdr-left { display: flex; align-items: center; gap: 14px; }
.logo {
  font-family: var(--mono); font-size: 13px; font-weight: 600;
  color: var(--cyan); letter-spacing: .04em;
  display: flex; align-items: center; gap: 8px;
}
.logo-icon {
  width: 28px; height: 28px; border-radius: 6px;
  background: linear-gradient(135deg, var(--cyan-dim), rgba(56,189,248,.04));
  border: 1px solid rgba(56,189,248,.25);
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
}
.dev-tag {
  font-family: var(--mono); font-size: 9px; font-weight: 600;
  letter-spacing: .1em; padding: 2px 7px; border-radius: 3px;
  background: rgba(248,113,113,.15); color: var(--red);
  border: 1px solid rgba(248,113,113,.25);
  animation: blink 2.5s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.5} }

.hdr-right { display: flex; align-items: center; gap: 20px; }
.live-indicator { display: flex; align-items: center; gap: 7px; }
.pulse {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--green);
  box-shadow: 0 0 0 0 rgba(74,222,128,.4);
  animation: heartbeat 2s ease-in-out infinite;
}
@keyframes heartbeat {
  0%   { box-shadow: 0 0 0 0   rgba(74,222,128,.5); }
  40%  { box-shadow: 0 0 0 5px rgba(74,222,128,.0); }
  100% { box-shadow: 0 0 0 0   rgba(74,222,128,.0); }
}
.live-text { font-family: var(--mono); font-size: 10px; color: var(--green); letter-spacing: .08em; }
#last-update { font-family: var(--mono); font-size: 10px; color: var(--text3); }

/* ── Layout ───────────────────────────────────────────────────────────── */
.main { position: relative; z-index: 1; padding: 24px 28px; max-width: 1680px; margin: 0 auto; }

/* ── Stats Grid ───────────────────────────────────────────────────────── */
.stats { display: grid; grid-template-columns: repeat(9, 1fr); gap: 10px; margin-bottom: 24px; }
@media (max-width: 1400px) { .stats { grid-template-columns: repeat(5, 1fr); } }
@media (max-width: 900px)  { .stats { grid-template-columns: repeat(3, 1fr); } }

.stat {
  background: var(--s1); border: 1px solid var(--border);
  border-radius: var(--r); padding: 14px 16px;
  position: relative; overflow: hidden;
  transition: border-color .2s, transform .15s;
  cursor: default;
}
.stat:hover { border-color: var(--border2); transform: translateY(-1px); }
.stat::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--accent, var(--cyan)), transparent);
  opacity: .5;
}
.stat:nth-child(1)  { --accent: var(--cyan); }
.stat:nth-child(2)  { --accent: var(--green); }
.stat:nth-child(3)  { --accent: var(--amber); }
.stat:nth-child(4)  { --accent: var(--red); }
.stat:nth-child(5)  { --accent: var(--violet); }
.stat:nth-child(6)  { --accent: #34d399; }
.stat:nth-child(7)  { --accent: var(--red); }
.stat:nth-child(8)  { --accent: var(--green); }
.stat:nth-child(9)  { --accent: var(--amber); }

.stat-lbl {
  font-family: var(--mono); font-size: 9px; font-weight: 500;
  letter-spacing: .1em; text-transform: uppercase;
  color: var(--text3); margin-bottom: 8px;
}
.stat-val {
  font-family: var(--mono); font-size: 26px; font-weight: 600;
  color: var(--text); line-height: 1;
  transition: color .3s;
}
.stat-val.updated { color: var(--accent, var(--cyan)); }

/* ── Tabs ─────────────────────────────────────────────────────────────── */
.tabs-bar {
  display: flex; gap: 2px; margin-bottom: 16px;
  background: var(--s1); border: 1px solid var(--border);
  border-radius: var(--r); padding: 4px; width: fit-content;
}
.tab-btn {
  font-family: var(--mono); font-size: 10px; font-weight: 500;
  letter-spacing: .06em; text-transform: uppercase;
  padding: 7px 14px; border-radius: 4px;
  border: none; background: transparent;
  color: var(--text2); cursor: pointer;
  transition: all .15s; white-space: nowrap;
  display: flex; align-items: center; gap: 6px;
}
.tab-btn:hover { color: var(--text); background: var(--s2); }
.tab-btn.active {
  background: var(--s3); color: var(--cyan);
  border: 1px solid var(--border2);
}
.tab-icon { font-size: 11px; }

/* ── Panel ────────────────────────────────────────────────────────────── */
.panel {
  background: var(--s1); border: 1px solid var(--border);
  border-radius: var(--r); overflow: hidden;
}
.panel-hdr {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px; border-bottom: 1px solid var(--border);
  flex-wrap: wrap; gap: 10px;
}
.panel-title {
  font-family: var(--mono); font-size: 11px; font-weight: 600;
  letter-spacing: .08em; text-transform: uppercase;
  color: var(--cyan); display: flex; align-items: center; gap: 8px;
}
.row-count {
  font-family: var(--mono); font-size: 10px;
  color: var(--text3); background: var(--s2);
  padding: 2px 8px; border-radius: 20px; border: 1px solid var(--border);
}

/* ── Toolbar ──────────────────────────────────────────────────────────── */
.toolbar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.inp {
  background: var(--s2); border: 1px solid var(--border);
  color: var(--text); font-family: var(--mono); font-size: 11px;
  padding: 6px 11px; border-radius: var(--r); outline: none;
  transition: border-color .15s;
}
.inp:focus { border-color: rgba(56,189,248,.4); }
.inp::placeholder { color: var(--text3); }
.inp-search { width: 200px; }
.inp-search:focus { width: 240px; }

.sel {
  background: var(--s2); border: 1px solid var(--border);
  color: var(--text2); font-family: var(--mono); font-size: 11px;
  padding: 6px 11px; border-radius: var(--r); outline: none;
  cursor: pointer; transition: border-color .15s;
}
.sel:focus { border-color: rgba(56,189,248,.4); }

.btn {
  font-family: var(--mono); font-size: 10px; font-weight: 600;
  letter-spacing: .05em; padding: 6px 12px; border-radius: var(--r);
  border: 1px solid; cursor: pointer; transition: all .15s;
  display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;
}
.btn:disabled { opacity: .4; cursor: default; }
.btn-ghost  { background: transparent; border-color: var(--border); color: var(--text2); }
.btn-ghost:hover:not(:disabled) { border-color: var(--border2); color: var(--text); background: var(--s2); }
.btn-cyan   { background: var(--cyan-dim); border-color: rgba(56,189,248,.3); color: var(--cyan); }
.btn-cyan:hover:not(:disabled)  { background: rgba(56,189,248,.2); }
.btn-green  { background: var(--green-dim); border-color: rgba(74,222,128,.3); color: var(--green); }
.btn-green:hover:not(:disabled) { background: rgba(74,222,128,.2); }
.btn-red    { background: var(--red-dim);   border-color: rgba(248,113,113,.3); color: var(--red); }
.btn-red:hover:not(:disabled)   { background: rgba(248,113,113,.2); }
.btn-amber  { background: var(--amber-dim); border-color: rgba(251,191,36,.3); color: var(--amber); }
.btn-amber:hover:not(:disabled) { background: rgba(251,191,36,.2); }
.btn-sm     { padding: 3px 9px; font-size: 9px; }

/* Export button */
.btn-export {
  background: transparent; border-color: var(--border);
  color: var(--text3); font-size: 9px; padding: 5px 10px;
}
.btn-export:hover { border-color: var(--amber); color: var(--amber); }

/* ── Add form ─────────────────────────────────────────────────────────── */
.add-bar {
  display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
  padding: 10px 20px; border-bottom: 1px solid var(--border);
  background: rgba(56,189,248,.02);
}
.add-lbl { font-family: var(--mono); font-size: 9px; color: var(--text3); letter-spacing: .1em; text-transform: uppercase; }
.inp-sm  { padding: 5px 10px; font-size: 11px; }

/* ── Table ────────────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }

/* Sticky header */
.tbl-scroll {
  max-height: 580px; overflow-y: auto;
  scrollbar-width: thin; scrollbar-color: var(--border2) transparent;
}
.tbl-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
.tbl-scroll::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

table { width: 100%; border-collapse: collapse; font-size: 12px; }

thead { position: sticky; top: 0; z-index: 10; }
thead tr { background: var(--s2); }
th {
  padding: 9px 16px; text-align: left;
  font-family: var(--mono); font-size: 9px; font-weight: 500;
  letter-spacing: .08em; text-transform: uppercase; color: var(--text3);
  white-space: nowrap; border-bottom: 1px solid var(--border);
  user-select: none;
}

td {
  padding: 9px 16px; border-bottom: 1px solid var(--border);
  vertical-align: middle; white-space: nowrap;
}
tr:last-child td { border-bottom: none; }

tbody tr { transition: background .1s; }
tbody tr:hover { background: var(--s2); }
tbody tr.row-blocked { background: rgba(248,113,113,.03); }
tbody tr.row-blocked:hover { background: rgba(248,113,113,.06); }
tbody tr.row-wl { background: rgba(74,222,128,.02); }

/* ── Badges ───────────────────────────────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center;
  padding: 2px 7px; border-radius: 3px;
  font-family: var(--mono); font-size: 9px; font-weight: 600;
  letter-spacing: .08em; text-transform: uppercase; border: 1px solid;
  white-space: nowrap;
}
.b-green   { background: var(--green-dim); color: var(--green); border-color: rgba(74,222,128,.25); }
.b-red     { background: var(--red-dim);   color: var(--red);   border-color: rgba(248,113,113,.25); }
.b-amber   { background: var(--amber-dim); color: var(--amber); border-color: rgba(251,191,36,.25); }
.b-cyan    { background: var(--cyan-dim);  color: var(--cyan);  border-color: rgba(56,189,248,.25); }
.b-violet  { background: rgba(167,139,250,.12); color: var(--violet); border-color: rgba(167,139,250,.25); }
.b-muted   { background: rgba(255,255,255,.04); color: var(--text3); border-color: var(--border); }
.b-perm    { background: rgba(248,113,113,.2); color: var(--red); border-color: rgba(248,113,113,.4); font-size: 8px; }

/* ── Misc cells ───────────────────────────────────────────────────────── */
.mono-sm { font-family: var(--mono); font-size: 11px; color: var(--text2); }
.dim     { color: var(--text3); font-size: 11px; }
.trunc   { max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fp-cell { font-family: var(--mono); font-size: 10px; color: var(--text3); max-width: 130px; overflow: hidden; text-overflow: ellipsis; }
.user-cell strong { color: var(--text); font-weight: 500; }

/* Risk bar */
.risk { display: flex; align-items: center; gap: 7px; }
.risk-bar { width: 48px; height: 3px; background: var(--s3); border-radius: 2px; overflow: hidden; flex-shrink: 0; }
.risk-fill { height: 100%; border-radius: 2px; transition: width .3s; }
.risk-num  { font-family: var(--mono); font-size: 11px; font-weight: 600; min-width: 22px; }

/* Flags */
.flags { display: flex; flex-wrap: wrap; gap: 3px; max-width: 180px; }
.flag  { font-family: var(--mono); font-size: 9px; padding: 1px 5px; border-radius: 2px; background: rgba(251,191,36,.08); color: var(--amber); border: 1px solid rgba(251,191,36,.15); }

/* OTP code */
.otp-tok { font-family: var(--mono); font-size: 10px; letter-spacing: .1em; color: var(--amber); }

/* ── Sentinel / Load-more ─────────────────────────────────────────────── */
.sentinel { height: 1px; }
.load-more-row td {
  text-align: center; padding: 16px;
  color: var(--text3); font-family: var(--mono); font-size: 10px;
}

/* ── States ───────────────────────────────────────────────────────────── */
.state-row td {
  text-align: center; padding: 48px 16px;
  color: var(--text3); font-family: var(--mono); font-size: 11px;
}
.spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid var(--border2); border-top-color: var(--cyan); border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Tab content ─────────────────────────────────────────────────────── */
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── Toast ────────────────────────────────────────────────────────────── */
.toast {
  position: fixed; bottom: 20px; right: 20px; z-index: 9999;
  padding: 11px 18px; border-radius: var(--r);
  font-family: var(--mono); font-size: 11px; font-weight: 500;
  opacity: 0; transform: translateY(6px);
  transition: all .25s cubic-bezier(.34,1.56,.64,1);
  pointer-events: none; max-width: 380px;
  display: flex; align-items: center; gap: 8px;
}
.toast.show { opacity: 1; transform: translateY(0); }
.toast-ok  { background: #0a1f12; border: 1px solid rgba(74,222,128,.35);  color: var(--green); }
.toast-err { background: #1f0a0a; border: 1px solid rgba(248,113,113,.35); color: var(--red);   }

/* ── Confirm modal ───────────────────────────────────────────────────── */
.overlay {
  position: fixed; inset: 0; background: rgba(7,9,15,.8);
  backdrop-filter: blur(4px); z-index: 500;
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none; transition: opacity .2s;
}
.overlay.open { opacity: 1; pointer-events: all; }
.modal {
  background: var(--s2); border: 1px solid var(--border2);
  border-radius: 10px; padding: 28px 32px; max-width: 420px; width: 90%;
  transform: scale(.95); transition: transform .2s;
}
.overlay.open .modal { transform: scale(1); }
.modal-title { font-family: var(--mono); font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 10px; }
.modal-body  { color: var(--text2); font-size: 12px; margin-bottom: 20px; line-height: 1.6; }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
.modal-inp { width: 100%; margin-bottom: 12px; padding: 7px 12px; }

/* ── Scrollbar global ────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }
</style>
</head>
<body>

<!-- Header -->
<header class="hdr">
  <div class="hdr-left">
    <div class="logo">
      <div class="logo-icon">🔐</div>
      AUTH MONITOR
    </div>
    <span class="dev-tag">DEV ONLY</span>
  </div>
  <div class="hdr-right">
    <div class="live-indicator">
      <div class="pulse"></div>
      <span class="live-text">LIVE</span>
    </div>
    <span id="last-update">—</span>
  </div>
</header>

<main class="main">

  <!-- Stats -->
  <div class="stats">
    <div class="stat"><div class="stat-lbl">Total Users</div><div class="stat-val" id="s-users">—</div></div>
    <div class="stat"><div class="stat-lbl">Active Users</div><div class="stat-val" id="s-active">—</div></div>
    <div class="stat"><div class="stat-lbl">Login Logs</div><div class="stat-val" id="s-logs">—</div></div>
    <div class="stat"><div class="stat-lbl">Blocked Logs</div><div class="stat-val" id="s-blocked">—</div></div>
    <div class="stat"><div class="stat-lbl">Active OTPs</div><div class="stat-val" id="s-otps">—</div></div>
    <div class="stat"><div class="stat-lbl">Trusted Devices</div><div class="stat-val" id="s-devices">—</div></div>
    <div class="stat"><div class="stat-lbl">IP Blacklisted</div><div class="stat-val" id="s-blacklist">—</div></div>
    <div class="stat"><div class="stat-lbl">IP Whitelisted</div><div class="stat-val" id="s-whitelist">—</div></div>
    <div class="stat"><div class="stat-lbl">Users Blocked</div><div class="stat-val" id="s-userblocks">—</div></div>
  </div>

  <!-- Tabs -->
  <div class="tabs-bar">
    <button class="tab-btn active" onclick="switchTab('otp')"><span class="tab-icon">🔐</span>OTP</button>
    <button class="tab-btn" onclick="switchTab('logs')"><span class="tab-icon">📋</span>Logs</button>
    <button class="tab-btn" onclick="switchTab('devices')"><span class="tab-icon">💻</span>Devices</button>
    <button class="tab-btn" onclick="switchTab('users')"><span class="tab-icon">👥</span>Users</button>
    <button class="tab-btn" onclick="switchTab('blacklist')"><span class="tab-icon">🚫</span>Blacklist</button>
    <button class="tab-btn" onclick="switchTab('whitelist')"><span class="tab-icon">✅</span>Whitelist</button>
  </div>

  <!-- OTP -->
  <div class="tab-panel active" id="tp-otp">
    <div class="panel">
      <div class="panel-hdr">
        <div class="panel-title">OTP Verifications <span class="row-count" id="cnt-otp">0</span></div>
        <div class="toolbar">
          <select class="sel" id="f-otp-status" onchange="resetLoad('otp')"><option value="">All Status</option><option value="active">Active</option><option value="verified">Verified</option><option value="expired">Expired</option></select>
          <input class="inp inp-search" id="f-otp-q" placeholder="Search user / email…" oninput="debounce('otp')">
          <button class="btn btn-ghost" onclick="resetLoad('otp')">↻ Refresh</button>
        </div>
      </div>
      <div class="tbl-wrap"><div class="tbl-scroll" id="sc-otp">
        <table>
          <thead><tr><th>#</th><th>User</th><th>Email</th><th>Token</th><th>Status</th><th>Attempts</th><th>Expires At</th><th>Verified At</th><th>Created</th></tr></thead>
          <tbody id="tb-otp"><tr class="state-row"><td colspan="9"><span class="spinner"></span>Loading…</td></tr></tbody>
        </table>
      </div></div>
    </div>
  </div>

  <!-- Logs -->
  <div class="tab-panel" id="tp-logs">
    <div class="panel">
      <div class="panel-hdr">
        <div class="panel-title">Login Logs <span class="row-count" id="cnt-logs">0</span></div>
        <div class="toolbar">
          <select class="sel" id="f-logs-status" onchange="resetLoad('logs')"><option value="">All Status</option><option value="success">Success</option><option value="otp_required">OTP Required</option><option value="blocked">Blocked</option><option value="failed">Failed</option></select>
          <select class="sel" id="f-logs-decision" onchange="resetLoad('logs')"><option value="">All Decision</option><option value="allow">ALLOW</option><option value="otp">OTP</option><option value="block">BLOCK</option></select>
          <input class="inp inp-search" id="f-logs-q" placeholder="Search user / IP…" oninput="debounce('logs')">
          <button class="btn btn-export btn-sm" onclick="exportCSV()">↓ Export CSV</button>
          <button class="btn btn-ghost" onclick="resetLoad('logs')">↻ Refresh</button>
        </div>
      </div>
      <div class="tbl-wrap"><div class="tbl-scroll" id="sc-logs">
        <table>
          <thead><tr><th>#</th><th>Time</th><th>User</th><th>Email</th><th>IP</th><th>Status</th><th>Decision</th><th>Risk</th><th>Flags</th><th>Fingerprint</th></tr></thead>
          <tbody id="tb-logs"><tr class="state-row"><td colspan="10"><span class="spinner"></span>Loading…</td></tr></tbody>
        </table>
      </div></div>
    </div>
  </div>

  <!-- Devices -->
  <div class="tab-panel" id="tp-devices">
    <div class="panel">
      <div class="panel-hdr">
        <div class="panel-title">Trusted Devices <span class="row-count" id="cnt-devices">0</span></div>
        <div class="toolbar">
          <select class="sel" id="f-dev-status" onchange="resetLoad('devices')"><option value="">All</option><option value="trusted">Trusted</option><option value="revoked">Revoked</option></select>
          <input class="inp inp-search" id="f-dev-q" placeholder="Search user / IP…" oninput="debounce('devices')">
          <button class="btn btn-ghost" onclick="resetLoad('devices')">↻ Refresh</button>
        </div>
      </div>
      <div class="tbl-wrap"><div class="tbl-scroll" id="sc-devices">
        <table>
          <thead><tr><th>#</th><th>User</th><th>Email</th><th>Label</th><th>Fingerprint</th><th>IP</th><th>Status</th><th>Last Seen</th><th>Trusted Until</th><th>Action</th></tr></thead>
          <tbody id="tb-devices"><tr class="state-row"><td colspan="10"><span class="spinner"></span>Loading…</td></tr></tbody>
        </table>
      </div></div>
    </div>
  </div>

  <!-- Users -->
  <div class="tab-panel" id="tp-users">
    <div class="panel">
      <div class="panel-hdr">
        <div class="panel-title">Users <span class="row-count" id="cnt-users">0</span></div>
        <div class="toolbar">
          <select class="sel" id="f-usr-status" onchange="resetLoad('users')"><option value="">All Status</option><option value="blocked">Blocked</option><option value="ok">OK</option></select>
          <input class="inp inp-search" id="f-usr-q" placeholder="Search name / email…" oninput="debounce('users')">
          <button class="btn btn-ghost" onclick="resetLoad('users')">↻ Refresh</button>
        </div>
      </div>
      <div class="tbl-wrap"><div class="tbl-scroll" id="sc-users">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Active</th><th>Verified</th><th>Last Login</th><th>Last IP</th><th>Logs</th><th>Devices</th><th>Created</th><th>Status</th><th>Action</th></tr></thead>
          <tbody id="tb-users"><tr class="state-row"><td colspan="12"><span class="spinner"></span>Loading…</td></tr></tbody>
        </table>
      </div></div>
    </div>
  </div>

  <!-- Blacklist -->
  <div class="tab-panel" id="tp-blacklist">
    <div class="panel">
      <div class="panel-hdr">
        <div class="panel-title">IP Blacklist <span class="row-count" id="cnt-blacklist">0</span></div>
        <div class="toolbar">
          <input class="inp inp-search" id="f-bl-q" placeholder="Search IP / reason…" oninput="debounce('blacklist')">
          <button class="btn btn-ghost" onclick="resetLoad('blacklist')">↻ Refresh</button>
        </div>
      </div>
      <div class="add-bar">
        <span class="add-lbl">Add IP →</span>
        <input class="inp inp-sm" id="bl-ip"      placeholder="IP Address"          style="width:155px">
        <input class="inp inp-sm" id="bl-reason"  placeholder="Reason (optional)"   style="width:190px">
        <input class="inp inp-sm" id="bl-minutes" placeholder="Minutes (blank=perm)" style="width:170px" type="number" min="1">
        <button class="btn btn-red btn-sm" onclick="addBlacklist()">+ Blacklist</button>
      </div>
      <div class="tbl-wrap"><div class="tbl-scroll" id="sc-blacklist">
        <table>
          <thead><tr><th>#</th><th>IP Address</th><th>Reason</th><th>By</th><th>Count</th><th>Blocked Until</th><th>Blocked At</th><th>Status</th><th>Action</th></tr></thead>
          <tbody id="tb-blacklist"><tr class="state-row"><td colspan="9"><span class="spinner"></span>Loading…</td></tr></tbody>
        </table>
      </div></div>
    </div>
  </div>

  <!-- Whitelist -->
  <div class="tab-panel" id="tp-whitelist">
    <div class="panel">
      <div class="panel-hdr">
        <div class="panel-title">IP Whitelist <span class="row-count" id="cnt-whitelist">0</span></div>
        <div class="toolbar">
          <input class="inp inp-search" id="f-wl-q" placeholder="Search IP / label…" oninput="debounce('whitelist')">
          <button class="btn btn-ghost" onclick="resetLoad('whitelist')">↻ Refresh</button>
        </div>
      </div>
      <div class="add-bar">
        <span class="add-lbl">Add IP →</span>
        <input class="inp inp-sm" id="wl-ip"    placeholder="IP Address"          style="width:155px">
        <input class="inp inp-sm" id="wl-label" placeholder="Label (e.g. Office)" style="width:220px">
        <button class="btn btn-green btn-sm" onclick="addWhitelist()">+ Whitelist</button>
      </div>
      <div class="tbl-wrap"><div class="tbl-scroll" id="sc-whitelist">
        <table>
          <thead><tr><th>#</th><th>IP Address</th><th>Label</th><th>Added By</th><th>Created</th><th>Action</th></tr></thead>
          <tbody id="tb-whitelist"><tr class="state-row"><td colspan="6"><span class="spinner"></span>Loading…</td></tr></tbody>
        </table>
      </div></div>
    </div>
  </div>

</main>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- Confirm Modal -->
<div class="overlay" id="overlay">
  <div class="modal">
    <div class="modal-title" id="m-title">Confirm</div>
    <div class="modal-body"  id="m-body"></div>
    <input class="inp modal-inp" id="m-inp" placeholder="" type="text" style="display:none">
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-cyan"  id="m-ok">Confirm</button>
    </div>
  </div>
</div>

<script>
'use strict';

const BASE = '/dev/monitoring/api';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Cursor-pagination state per tab ───────────────────────────────────────
const state = {};
const TABS  = ['otp','logs','devices','users','blacklist','whitelist'];

function initState(tab) {
  state[tab] = { cursor: 0, hasMore: true, loading: false, count: 0 };
}
TABS.forEach(initState);

// ── Active tab ────────────────────────────────────────────────────────────
let activeTab = 'otp';

function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach((el, i) => {
    el.classList.toggle('active', TABS[i] === tab);
  });
  document.querySelectorAll('.tab-panel').forEach(el => el.classList.remove('active'));
  document.getElementById('tp-' + tab).classList.add('active');
  activeTab = tab;
  if (!state[tab] || state[tab].count === 0) resetLoad(tab);
}

// ── Debounce search ───────────────────────────────────────────────────────
const debounceTimers = {};
function debounce(tab) {
  clearTimeout(debounceTimers[tab]);
  debounceTimers[tab] = setTimeout(() => resetLoad(tab), 320);
}

function resetLoad(tab) {
  initState(tab);
  document.getElementById('tb-' + tab).innerHTML =
    stateRow(getColCount(tab), '<span class="spinner"></span>Loading…');
  loadPage(tab);
}

// ── Infinite scroll via IntersectionObserver ──────────────────────────────
const observers = {};

function setupObserver(tab) {
  if (observers[tab]) observers[tab].disconnect();
  const sentinel = document.getElementById('sentinel-' + tab);
  if (!sentinel) return;
  observers[tab] = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting && state[tab].hasMore && !state[tab].loading) {
      loadPage(tab);
    }
  }, { rootMargin: '200px' });
  observers[tab].observe(sentinel);
}

// ── Core loader ───────────────────────────────────────────────────────────
async function loadPage(tab) {
  const s = state[tab];
  if (!s.hasMore || s.loading) return;
  s.loading = true;

  const params = buildParams(tab, s.cursor);
  const endpoint = tabEndpoint(tab);

  try {
    const resp = await fetch(BASE + endpoint + '?' + params, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    });
    const json = await resp.json();
    if (!resp.ok) throw new Error(json.message || 'HTTP ' + resp.status);

    const rows  = json.data ?? json;          // fallback for array response
    const isArr = Array.isArray(json);
    s.hasMore   = isArr ? false : (json.has_more ?? false);
    s.cursor    = isArr ? 0 : (json.next_cursor ?? 0);
    s.count    += rows.length;
    s.loading   = false;

    renderRows(tab, rows, s.count - rows.length);
    updateCount(tab, s.count, s.hasMore);
    if (s.hasMore) setupObserver(tab);

  } catch (e) {
    s.loading = false;
    const tb = document.getElementById('tb-' + tab);
    const existing = tb.querySelectorAll('tr:not(.state-row)').length;
    if (existing === 0) {
      tb.innerHTML = stateRow(getColCount(tab), '⚠ ' + esc(e.message));
    }
    toast(e.message, 'err');
  }
}

function buildParams(tab, cursor) {
  const p = new URLSearchParams();
  if (cursor) p.set('cursor', cursor);

  const qMap = {
    otp:       { status: 'f-otp-status', search: 'f-otp-q' },
    logs:      { status: 'f-logs-status', decision: 'f-logs-decision', search: 'f-logs-q' },
    devices:   { status: 'f-dev-status', search: 'f-dev-q' },
    users:     { status: 'f-usr-status', search: 'f-usr-q' },
    blacklist: { search: 'f-bl-q' },
    whitelist: { search: 'f-wl-q' },
  };

  Object.entries(qMap[tab] || {}).forEach(([key, id]) => {
    const el = document.getElementById(id);
    if (el && el.value) p.set(key, el.value);
  });

  return p.toString();
}

function tabEndpoint(tab) {
  return {
    otp:       '/otps',
    logs:      '/logs',
    devices:   '/devices',
    users:     '/users',
    blacklist: '/ip-blacklist',
    whitelist: '/ip-whitelist',
  }[tab];
}

function getColCount(tab) {
  return { otp: 9, logs: 10, devices: 10, users: 12, blacklist: 9, whitelist: 6 }[tab] || 9;
}

// ── Row renderers ─────────────────────────────────────────────────────────
function renderRows(tab, rows, offset) {
  const tb = document.getElementById('tb-' + tab);

  // Clear placeholder on first page
  if (offset === 0) tb.innerHTML = '';
  else {
    const sentinel = document.getElementById('sentinel-' + tab);
    if (sentinel) sentinel.parentElement.remove();
  }

  if (rows.length === 0 && offset === 0) {
    tb.innerHTML = stateRow(getColCount(tab), 'No data found');
    return;
  }

  const html = rows.map((r, i) => renderRow(tab, r, offset + i + 1)).join('');
  tb.insertAdjacentHTML('beforeend', html);

  // Sentinel row for infinite scroll
  if (state[tab].hasMore) {
    tb.insertAdjacentHTML('beforeend', `
      <tr id="sentinel-row-${tab}">
        <td colspan="${getColCount(tab)}" style="padding:0;border:none">
          <div id="sentinel-${tab}" class="sentinel"></div>
        </td>
      </tr>`);
  }
}

function renderRow(tab, r, n) {
  switch (tab) {
    case 'otp':       return rowOtp(r, n);
    case 'logs':      return rowLog(r, n);
    case 'devices':   return rowDevice(r, n);
    case 'users':     return rowUser(r, n);
    case 'blacklist': return rowBlacklist(r, n);
    case 'whitelist': return rowWhitelist(r, n);
    default: return '';
  }
}

function rowOtp(o, n) {
  const s = o.status;
  const bc = s === 'verified' ? 'b-green' : s === 'expired' ? 'b-muted' : 'b-amber';
  return `<tr>
    <td class="dim">${n}</td>
    <td class="user-cell"><strong>${esc(o.user)}</strong></td>
    <td class="dim">${esc(o.email)}</td>
    <td><span class="otp-tok">${esc(o.otp_code)}</span></td>
    <td><span class="badge ${bc}">${s}</span></td>
    <td style="color:${o.attempts>=3?'var(--red)':'inherit'}">${o.attempts}/3</td>
    <td class="dim">${o.expires_at||'—'}</td>
    <td class="dim">${o.verified_at||'—'}</td>
    <td class="dim">${o.created_at}</td>
  </tr>`;
}

function rowLog(l, n) {
  const sc = l.risk_score ?? 0;
  const rc = sc < 30 ? 'var(--green)' : sc < 60 ? 'var(--amber)' : 'var(--red)';
  const sb = { success:'b-green', otp_required:'b-amber', blocked:'b-red', failed:'b-red' }[l.status] || 'b-muted';
  const db = { ALLOW:'b-green', OTP:'b-amber', BLOCK:'b-red' }[l.decision] || 'b-muted';
  const flags = Array.isArray(l.reason_flags) ? l.reason_flags
              : (l.reason_flags ? tryParse(l.reason_flags) : []);
  return `<tr class="${l.decision==='BLOCK'?'row-blocked':''}">
    <td class="dim">${n}</td>
    <td class="mono-sm">${l.occurred_at||'—'}</td>
    <td class="user-cell"><strong>${esc(l.user)}</strong></td>
    <td class="dim">${esc(l.email)}</td>
    <td class="mono-sm">${esc(l.ip_address||'—')}</td>
    <td><span class="badge ${sb}">${l.status}</span></td>
    <td><span class="badge ${db}">${l.decision||'—'}</span></td>
    <td>
      <div class="risk">
        <span class="risk-num" style="color:${rc}">${sc}</span>
        <div class="risk-bar"><div class="risk-fill" style="width:${Math.min(sc,100)}%;background:${rc}"></div></div>
      </div>
    </td>
    <td><div class="flags">${flags.map(f=>`<span class="flag">${esc(f)}</span>`).join('')||'—'}</div></td>
    <td><span class="fp-cell" title="${esc(l.device_fp)}">${esc(l.device_fp||'—')}</span></td>
  </tr>`;
}

function rowDevice(d, n) {
  const rev = d.is_revoked;
  return `<tr>
    <td class="dim">${n}</td>
    <td class="user-cell"><strong>${esc(d.user)}</strong></td>
    <td class="dim">${esc(d.email)}</td>
    <td>${esc(d.device_label)}</td>
    <td><span class="fp-cell" title="${esc(d.fingerprint)}">${esc(d.fingerprint)}</span></td>
    <td class="mono-sm">${esc(d.ip_address||'—')}</td>
    <td><span class="badge ${rev?'b-red':'b-green'}">${rev?'REVOKED':'TRUSTED'}</span></td>
    <td class="dim">${d.last_seen||'—'}</td>
    <td class="dim">${d.trusted_until||'—'}</td>
    <td>
      <button class="btn btn-sm ${rev?'btn-green':'btn-red'}" onclick="toggleDevice(${d.id},this)">
        ${rev?'↺ Restore':'⊘ Revoke'}
      </button>
    </td>
  </tr>`;
}

function rowUser(u, n) {
  const blocked = u.is_blocked;
  return `<tr class="${blocked?'row-blocked':''}">
    <td class="dim">${u.id}</td>
    <td class="user-cell"><strong>${esc(u.name)}</strong></td>
    <td class="dim">${esc(u.email)}</td>
    <td><span class="badge ${u.is_active?'b-green':'b-muted'}">${u.is_active?'YES':'NO'}</span></td>
    <td><span class="badge ${u.verified?'b-cyan':'b-muted'}">${u.verified?'YES':'NO'}</span></td>
    <td class="dim">${u.last_login_at||'—'}</td>
    <td class="mono-sm">${esc(u.last_login_ip||'—')}</td>
    <td>${u.login_count}</td>
    <td>${u.device_count}</td>
    <td class="dim">${u.created_at}</td>
    <td><span class="badge ${blocked?'b-red':'b-green'}">${blocked?'BLOCKED':'OK'}</span></td>
    <td>
      ${blocked
        ? `<button class="btn btn-cyan btn-sm" onclick="unblockUser(${u.id},'${esc(u.name)}',this)">↑ Unblock</button>`
        : `<button class="btn btn-red  btn-sm" onclick="blockUser(${u.id},'${esc(u.name)}',this)">⊘ Block</button>`
      }
    </td>
  </tr>`;
}

function rowBlacklist(r, n) {
  const act = r.is_active;
  const by  = r.blocked_by === 'auto' ? 'b-violet' : 'b-cyan';
  const pu  = r.blocked_until === 'Permanen';
  return `<tr class="${act?'row-blocked':''}">
    <td class="dim">${n}</td>
    <td class="mono-sm"><strong>${esc(r.ip_address)}</strong></td>
    <td class="dim trunc" style="max-width:200px">${esc(r.reason)}</td>
    <td><span class="badge ${by}">${r.blocked_by}</span></td>
    <td>${r.block_count}</td>
    <td>${pu?`<span class="badge b-perm">PERMANENT</span>`:(`<span class="dim">${r.blocked_until}</span>`)}</td>
    <td class="dim">${r.blocked_at}</td>
    <td><span class="badge ${act?'b-red':'b-muted'}">${act?'ACTIVE':'EXPIRED'}</span></td>
    <td><button class="btn btn-amber btn-sm" onclick="removeBlacklist('${esc(r.ip_address)}',this)">✕ Remove</button></td>
  </tr>`;
}

function rowWhitelist(r, n) {
  return `<tr class="row-wl">
    <td class="dim">${n}</td>
    <td class="mono-sm"><strong>${esc(r.ip_address)}</strong></td>
    <td>${esc(r.label)}</td>
    <td class="dim">${esc(r.added_by)}</td>
    <td class="dim">${r.created_at}</td>
    <td><button class="btn btn-amber btn-sm" onclick="removeWhitelist('${esc(r.ip_address)}',this)">✕ Remove</button></td>
  </tr>`;
}

// ── Count badge ───────────────────────────────────────────────────────────
function updateCount(tab, n, hasMore) {
  const el = document.getElementById('cnt-' + tab);
  if (el) el.textContent = hasMore ? n + '+' : n;
}

// ── Actions ────────────────────────────────────────────────────────────────

async function toggleDevice(id, btn) {
  const isRev = btn.classList.contains('btn-red');
  const ok = await confirm2(`${isRev?'Revoke':'Restore'} this device?`);
  if (!ok) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('POST', `/devices/${id}/revoke`);
    toast(d.message, 'ok');
    setTimeout(() => resetLoad('devices'), 600);
  } catch(e) { toast(e.message,'err'); btn.disabled=false; }
}

async function unblockUser(userId, name, btn) {
  const ok = await confirm2(`Unblock user <strong>${esc(name)}</strong>?<br><br>This will: clear block cache, remove 10 latest block logs, restore all revoked devices, and reactivate the account.`);
  if (!ok) return;
  btn.disabled = true; btn.textContent = '…';
  try {
    const d = await apiFetch('POST', `/users/${userId}/unblock`);
    toast(d.message, 'ok');
    setTimeout(() => resetLoad('users'), 600);
    loadStats();
  } catch(e) { toast(e.message,'err'); btn.disabled=false; btn.textContent='↑ Unblock'; }
}

async function blockUser(userId, name, btn) {
  const minutes = await prompt2(`Block <strong>${esc(name)}</strong>`, 'Duration in minutes (blank = permanent):');
  if (minutes === null) return;
  btn.disabled = true; btn.textContent = '…';
  try {
    const body = { reason: 'Manual block via monitor' };
    if (minutes.trim()) body.minutes = parseInt(minutes);
    const d = await apiFetch('POST', `/users/${userId}/block`, body);
    toast(d.message, 'ok');
    setTimeout(() => resetLoad('users'), 600);
    loadStats();
  } catch(e) { toast(e.message,'err'); btn.disabled=false; btn.textContent='⊘ Block'; }
}

async function addBlacklist() {
  const ip      = v('bl-ip');
  const reason  = v('bl-reason');
  const minutes = v('bl-minutes');
  if (!ip) { toast('IP address is required', 'err'); return; }
  try {
    const body = { ip_address: ip };
    if (reason)  body.reason  = reason;
    if (minutes) body.minutes = parseInt(minutes);
    const d = await apiFetch('POST', '/ip-blacklist', body);
    toast(d.message, 'ok');
    ['bl-ip','bl-reason','bl-minutes'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
    resetLoad('blacklist'); loadStats();
  } catch(e) { toast(e.message,'err'); }
}

async function removeBlacklist(ip, btn) {
  const ok = await confirm2(`Remove <strong>${esc(ip)}</strong> from blacklist?`);
  if (!ok) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('DELETE', `/ip-blacklist/${encodeURIComponent(ip)}`);
    toast(d.message, 'ok');
    resetLoad('blacklist'); loadStats();
  } catch(e) { toast(e.message,'err'); btn.disabled=false; }
}

async function addWhitelist() {
  const ip    = v('wl-ip');
  const label = v('wl-label');
  if (!ip) { toast('IP address is required', 'err'); return; }
  try {
    const d = await apiFetch('POST', '/ip-whitelist', { ip_address: ip, label });
    toast(d.message, 'ok');
    ['wl-ip','wl-label'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
    resetLoad('whitelist'); loadStats();
  } catch(e) { toast(e.message,'err'); }
}

async function removeWhitelist(ip, btn) {
  const ok = await confirm2(`Remove <strong>${esc(ip)}</strong> from whitelist?`);
  if (!ok) return;
  btn.disabled = true;
  try {
    const d = await apiFetch('DELETE', `/ip-whitelist/${encodeURIComponent(ip)}`);
    toast(d.message, 'ok');
    resetLoad('whitelist'); loadStats();
  } catch(e) { toast(e.message,'err'); btn.disabled=false; }
}

function exportCSV() {
  const decision = document.getElementById('f-logs-decision')?.value || '';
  const url = BASE + '/export/logs' + (decision ? '?decision=' + decision : '');
  window.open(url, '_blank');
}

// ── Stats ─────────────────────────────────────────────────────────────────
async function loadStats() {
  try {
    const d = await apiFetch('GET', '/stats');
    const map = {
      's-users': d.users, 's-active': d.active_users, 's-logs': d.total_logs,
      's-blocked': d.blocked_logs, 's-otps': d.active_otps, 's-devices': d.trusted_devices,
      's-blacklist': d.ip_blacklisted, 's-whitelist': d.ip_whitelisted, 's-userblocks': d.users_blocked,
    };
    Object.entries(map).forEach(([id, val]) => {
      const el = document.getElementById(id);
      if (!el) return;
      if (el.textContent !== String(val)) {
        el.textContent = val;
        el.classList.add('updated');
        setTimeout(() => el.classList.remove('updated'), 1200);
      }
    });
    document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID');
  } catch(e) {}
}

// ── API helpers ───────────────────────────────────────────────────────────
async function apiFetch(method, path, body = null) {
  const opts = {
    method,
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
  };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(BASE + path, opts);
  const d = await r.json();
  if (!r.ok) throw new Error(d.message || 'HTTP ' + r.status);
  return d;
}

// ── Modal helpers ─────────────────────────────────────────────────────────
let modalResolve = null;

function confirm2(html) {
  return new Promise(resolve => {
    modalResolve = resolve;
    document.getElementById('m-title').textContent = 'Confirm Action';
    document.getElementById('m-body').innerHTML = html;
    document.getElementById('m-inp').style.display = 'none';
    document.getElementById('m-ok').textContent = 'Confirm';
    document.getElementById('m-ok').onclick = () => { closeModal(); resolve(true); };
    document.getElementById('overlay').classList.add('open');
  });
}

function prompt2(title, label) {
  return new Promise(resolve => {
    document.getElementById('m-title').innerHTML = title;
    document.getElementById('m-body').textContent = label;
    const inp = document.getElementById('m-inp');
    inp.style.display = ''; inp.value = '';
    document.getElementById('m-ok').textContent = 'OK';
    document.getElementById('m-ok').onclick = () => { const v = inp.value; closeModal(); resolve(v); };
    document.getElementById('overlay').classList.add('open');
    setTimeout(() => inp.focus(), 100);
  });
}

function closeModal() {
  document.getElementById('overlay').classList.remove('open');
  if (modalResolve) { modalResolve(null); modalResolve = null; }
}
document.getElementById('overlay').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });

// ── Utility ───────────────────────────────────────────────────────────────
function v(id) { return document.getElementById(id)?.value?.trim() ?? ''; }

function esc(str) {
  if (str == null) return '—';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function tryParse(val) {
  try { return JSON.parse(val); } catch { return []; }
}

function stateRow(cols, msg) {
  return `<tr class="state-row"><td colspan="${cols}">${msg}</td></tr>`;
}

let toastTimer;
function toast(msg, type = 'ok') {
  clearTimeout(toastTimer);
  const el = document.getElementById('toast');
  el.innerHTML = (type === 'ok' ? '✓ ' : '✕ ') + esc(msg);
  el.className = `toast toast-${type} show`;
  toastTimer = setTimeout(() => el.classList.remove('show'), 3500);
}

// ── Init ──────────────────────────────────────────────────────────────────
loadStats();
loadPage('otp');
setInterval(loadStats, 10000);
</script>
</body>
</html>