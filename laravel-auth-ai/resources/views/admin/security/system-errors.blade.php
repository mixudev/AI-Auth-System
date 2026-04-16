<div class="panel">
  <div class="panel-hdr">
    <div class="panel-title">
      ⚠️ System Error Logs
      <span class="row-count" id="cnt-errors">0</span>
    </div>
    <div class="toolbar">
      <input class="inp inp-search" id="f-errors-q" placeholder="Search title / IP / message…" oninput="debounce('errors')">
      <button class="btn btn-ghost" onclick="resetLoad('errors')">↻ Refresh</button>
    </div>
  </div>

  <div class="tbl-wrap">
    <div class="tbl-scroll" id="sc-errors">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Occurred At</th>
            <th>Category</th>
            <th>Event</th>
            <th>Title</th>
            <th>Message</th>
            <th>IP Address</th>
            <th style="width:100px">Action</th>
          </tr>
        </thead>
        <tbody id="tb-errors">
          <tr class="state-row"><td colspan="8"><span class="spinner"></span>Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
  .error-modal-content {
    background: #0f172a;
    color: #e2e8f0;
    padding: 20px;
    border-radius: 12px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    max-height: 70vh;
    overflow-y: auto;
    border: 1px solid rgba(255,255,255,0.1);
  }
  .error-meta-item {
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding-bottom: 10px;
  }
  .error-meta-label {
    color: #94a3b8;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
    display: block;
  }
  .error-trace {
    white-space: pre-wrap;
    background: #000;
    padding: 12px;
    border-radius: 6px;
    color: #f87171;
    overflow-x: auto;
    line-height: 1.5;
  }
</style>
