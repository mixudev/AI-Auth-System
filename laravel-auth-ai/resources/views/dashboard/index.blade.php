@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-sub', 'Ringkasan aktivitas akun Anda')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px;
        transition: border-color 0.2s;
    }
    .stat-card:hover { border-color: var(--border2); }
    .stat-label {
        font-size: 11px;
        font-family: var(--mono);
        color: var(--text3);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
    }
    .stat-value {
        font-size: 28px;
        font-family: var(--mono);
        font-weight: 700;
        color: var(--text);
        line-height: 1;
        margin-bottom: 6px;
    }
    .stat-value.green { color: var(--accent); }
    .stat-value.red { color: var(--danger); }
    .stat-value.yellow { color: var(--warn); }
    .stat-value.blue { color: var(--info); }
    .stat-sub { font-size: 11px; color: var(--text3); }

    .section-title {
        font-family: var(--mono);
        font-size: 12px;
        color: var(--text2);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

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
        font-size: 13px;
        color: var(--text2);
        border-bottom: 1px solid var(--border);
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: rgba(255,255,255,0.02); }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-family: var(--mono);
        font-weight: 700;
        letter-spacing: 0.05em;
    }
    .badge-allow { background: rgba(0,212,170,0.1); color: var(--accent); border: 1px solid rgba(0,212,170,0.2); }
    .badge-otp   { background: rgba(227,179,65,0.1); color: var(--warn);   border: 1px solid rgba(227,179,65,0.2); }
    .badge-block { background: rgba(248,81,73,0.1);  color: var(--danger); border: 1px solid rgba(248,81,73,0.2); }

    .mono { font-family: var(--mono); font-size: 12px; }
    .view-all {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-family: var(--mono);
        color: var(--text3);
        float: right;
        margin-top: -28px;
        margin-bottom: 16px;
    }
    .view-all:hover { color: var(--accent); }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text3);
        font-family: var(--mono);
        font-size: 12px;
    }
</style>
@endpush

@section('content')

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Login Berhasil</div>
        <div class="stat-value green">{{ $stats['total_logins'] }}</div>
        <div class="stat-sub">Sejak bergabung</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Login Diblokir</div>
        <div class="stat-value red">{{ $stats['blocked_count'] }}</div>
        <div class="stat-sub">Aktivitas mencurigakan</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Verifikasi OTP</div>
        <div class="stat-value yellow">{{ $stats['otp_count'] }}</div>
        <div class="stat-sub">Memerlukan konfirmasi tambahan</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Login Terakhir</div>
        <div class="stat-value blue" style="font-size:16px; margin-top:4px">
            {{ $stats['last_login'] ? $stats['last_login']->diffForHumans() : '—' }}
        </div>
        <div class="stat-sub">{{ $stats['last_login'] ? $stats['last_login']->format('d M Y, H:i') : 'Belum ada data' }}</div>
    </div>
</div>

{{-- Recent activity --}}
<a href="{{ route('audit.log') }}" class="view-all">Lihat semua →</a>
<div class="section-title">Aktivitas Terbaru</div>

<div class="table-wrap">
    @if($recentLogs->isEmpty())
        <div class="empty-state">Belum ada aktivitas login tercatat.</div>
    @else
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>IP Address</th>
                <th>Keputusan AI</th>
                <th>Risk Score</th>
                <th>Flags</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentLogs as $log)
            <tr>
                <td class="mono">{{ $log->occurred_at->format('d M Y H:i:s') }}</td>
                <td class="mono">{{ $log->ip_address ?? '—' }}</td>
                <td>
                    @php $d = strtolower($log->decision ?? 'allow') @endphp
                    <span class="badge badge-{{ $d }}">{{ strtoupper($d) }}</span>
                </td>
                <td class="mono">{{ $log->risk_score ?? '—' }}</td>
                <td style="font-size:11px; color:var(--text3)">
                    @if($log->reason_flags)
                        {{ implode(', ', is_array($log->reason_flags) ? $log->reason_flags : json_decode($log->reason_flags, true) ?? []) }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection
