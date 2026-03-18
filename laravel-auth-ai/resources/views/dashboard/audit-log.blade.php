@extends('layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('page-sub', 'Riwayat lengkap semua percobaan login')

@push('styles')
<style>
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    .filter-label {
        font-size: 11px;
        font-family: var(--mono);
        color: var(--text3);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .filter-btn {
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 11px;
        font-family: var(--mono);
        font-weight: 700;
        border: 1px solid var(--border);
        background: transparent;
        color: var(--text2);
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
        letter-spacing: 0.05em;
    }
    .filter-btn:hover { border-color: var(--border2); color: var(--text); }
    .filter-btn.active-all   { border-color: var(--border2); color: var(--text); background: var(--bg3); }
    .filter-btn.active-allow { border-color: rgba(0,212,170,0.4); color: var(--accent); background: rgba(0,212,170,0.08); }
    .filter-btn.active-otp   { border-color: rgba(227,179,65,0.4); color: var(--warn);   background: rgba(227,179,65,0.08); }
    .filter-btn.active-block { border-color: rgba(248,81,73,0.4);  color: var(--danger); background: rgba(248,81,73,0.08); }

    .table-wrap {
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    thead tr { border-bottom: 1px solid var(--border); }
    th {
        padding: 10px 16px;
        text-align: left;
        font-size: 11px;
        font-family: var(--mono);
        color: var(--text3);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 400;
    }
    td {
        padding: 12px 16px;
        font-size: 12px;
        color: var(--text2);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: rgba(255,255,255,0.02); }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-family: var(--mono);
        font-weight: 700;
        letter-spacing: 0.05em;
    }
    .badge-allow { background: rgba(0,212,170,0.1); color: var(--accent); border: 1px solid rgba(0,212,170,0.2); }
    .badge-otp   { background: rgba(227,179,65,0.1); color: var(--warn);   border: 1px solid rgba(227,179,65,0.2); }
    .badge-block { background: rgba(248,81,73,0.1);  color: var(--danger); border: 1px solid rgba(248,81,73,0.2); }

    .mono { font-family: var(--mono); font-size: 11px; }
    .flags { font-size: 11px; color: var(--text3); font-family: var(--mono); }
    .flag-tag {
        display: inline-block;
        background: var(--bg3);
        border: 1px solid var(--border);
        border-radius: 3px;
        padding: 1px 5px;
        font-size: 10px;
        margin: 1px;
        color: var(--text3);
    }
    .flag-tag.warn { border-color: rgba(227,179,65,0.3); color: var(--warn); }

    .pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border-top: 1px solid var(--border);
        font-size: 12px;
        font-family: var(--mono);
        color: var(--text3);
    }
    .pagination a, .pagination span {
        padding: 4px 9px;
        border-radius: 4px;
        border: 1px solid transparent;
        transition: all 0.15s;
    }
    .pagination a { color: var(--text2); border-color: var(--border); }
    .pagination a:hover { color: var(--text); border-color: var(--border2); }
    .pagination span.current { color: var(--accent); border-color: rgba(0,212,170,0.3); background: rgba(0,212,170,0.08); }

    .empty-state {
        text-align: center;
        padding: 60px;
        color: var(--text3);
        font-family: var(--mono);
        font-size: 12px;
    }
</style>
@endpush

@section('content')

<div class="filter-bar">
    <span class="filter-label">Filter:</span>
    <a href="{{ route('audit.log') }}" class="filter-btn {{ !request('filter') ? 'active-all' : '' }}">SEMUA</a>
    <a href="{{ route('audit.log', ['filter' => 'allow']) }}" class="filter-btn {{ request('filter') === 'allow' ? 'active-allow' : '' }}">ALLOW</a>
    <a href="{{ route('audit.log', ['filter' => 'otp']) }}"   class="filter-btn {{ request('filter') === 'otp'   ? 'active-otp'   : '' }}">OTP</a>
    <a href="{{ route('audit.log', ['filter' => 'block']) }}" class="filter-btn {{ request('filter') === 'block' ? 'active-block' : '' }}">BLOCK</a>
</div>

<div class="table-wrap">
    @if($logs->isEmpty())
        <div class="empty-state">Tidak ada data untuk filter ini.</div>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Waktu</th>
                <th>IP Address</th>
                <th>Keputusan</th>
                <th>Risk Score</th>
                <th>Confidence</th>
                <th>Flags</th>
                <th>Mode</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            @php $decision = strtolower($log->decision ?? 'allow') @endphp
            <tr>
                <td class="mono" style="color:var(--text3)">{{ $log->id }}</td>
                <td class="mono">{{ $log->occurred_at->format('d/m/y H:i:s') }}</td>
                <td class="mono">{{ $log->ip_address ?? '—' }}</td>
                <td><span class="badge badge-{{ $decision }}">{{ strtoupper($decision) }}</span></td>
                <td class="mono">
                    @if($log->risk_score !== null)
                        <span style="color: {{ $log->risk_score < 30 ? 'var(--accent)' : ($log->risk_score < 60 ? 'var(--warn)' : 'var(--danger)') }}">
                            {{ $log->risk_score }}
                        </span>
                    @else
                        —
                    @endif
                </td>
                <td class="mono">{{ $log->confidence ? number_format($log->confidence * 100, 0) . '%' : '—' }}</td>
                <td>
                    @php
                        $flags = is_array($log->reason_flags)
                            ? $log->reason_flags
                            : json_decode($log->reason_flags ?? '[]', true) ?? [];
                    @endphp
                    @foreach($flags as $flag)
                        <span class="flag-tag {{ str_contains($flag, 'vpn') || str_contains($flag, 'new') ? 'warn' : '' }}">
                            {{ $flag }}
                        </span>
                    @endforeach
                    @if(empty($flags)) <span style="color:var(--text3)">—</span> @endif
                </td>
                <td class="mono" style="color:var(--text3)">
                    {{ in_array('fallback_mode', $flags ?? []) ? '⚠ fallback' : 'ai' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($logs->hasPages())
    <div class="pagination">
        <span>Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} entri</span>
        <div style="display:flex; gap:4px; align-items:center">
            @if($logs->onFirstPage())
                <span>← Prev</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}">← Prev</a>
            @endif

            @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                @if($page == $logs->currentPage())
                    <span class="current">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}">Next →</a>
            @else
                <span>Next →</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>

@endsection
