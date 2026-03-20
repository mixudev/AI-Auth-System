@extends('layouts.app-dashboard')

@section('title', 'User Management')
@section('page-title', 'Users')
@section('page-sub', 'Kelola, monitor, dan kontrol akses semua pengguna sistem')

@section('content')

{{-- ─────────────────────────────────────────────────────────────────────────
     TOOLBAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Manajemen Pengguna</h1>
        <p class="text-xs text-slate-400 mt-0.5">Kelola, monitor, dan kontrol akses semua pengguna sistem</p>
    </div>
    <button
        onclick="openCreateModal()"
        class="flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm"
    >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Pengguna
    </button>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     STAT CARDS
───────────────────────────────────────────────────────────────────────── --}}
@php
$statCards = [
    ['label' => 'Total User',   'val' => $stats['total'],    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',         'color' => 'indigo',  'sub' => 'pengguna terdaftar'],
    ['label' => 'User Aktif',   'val' => $stats['active'],   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                    'color' => 'emerald', 'sub' => 'dapat login'],
    ['label' => 'Diblokir',     'val' => $stats['blocked'],  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>',                                                                                                               'color' => 'red',     'sub' => 'akun terkunci'],
    ['label' => 'Nonaktif',     'val' => $stats['inactive'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                                                                                                                                                'color' => 'amber',   'sub' => 'akun dinonaktifkan'],
    ['label' => 'Unverified',   'val' => $stats['unverified'],'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',                                                                                                       'color' => 'orange',  'sub' => 'email belum terverifikasi'],
    ['label' => 'Baru Hari Ini','val' => $stats['new_today'],'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',                                                                                                                        'color' => 'sky',     'sub' => 'registrasi baru'],
];
$colorMap = [
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-500/10',   'ic' => 'text-indigo-600 dark:text-indigo-400',   'ring' => 'ring-indigo-100 dark:ring-indigo-500/20'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'ic' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
    'red'     => ['bg' => 'bg-red-50 dark:bg-red-500/10',         'ic' => 'text-red-600 dark:text-red-400',         'ring' => 'ring-red-100 dark:ring-red-500/20'],
    'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-500/10',     'ic' => 'text-amber-600 dark:text-amber-400',     'ring' => 'ring-amber-100 dark:ring-amber-500/20'],
    'orange'  => ['bg' => 'bg-orange-50 dark:bg-orange-500/10',   'ic' => 'text-orange-600 dark:text-orange-400',   'ring' => 'ring-orange-100 dark:ring-orange-500/20'],
    'sky'     => ['bg' => 'bg-sky-50 dark:bg-sky-500/10',         'ic' => 'text-sky-600 dark:text-sky-400',         'ring' => 'ring-sky-100 dark:ring-sky-500/20'],
];
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @foreach($statCards as $card)
    @php $c = $colorMap[$card['color']]; @endphp
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 flex flex-col gap-3 hover:shadow-md dark:hover:shadow-black/20 transition-shadow">
        <div class="w-9 h-9 rounded-lg {{ $c['bg'] }} ring-1 {{ $c['ring'] }} flex items-center justify-center">
            <svg style="width:18px;height:18px" class="{{ $c['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $card['icon'] !!}</svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">{{ number_format($card['val']) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $card['label'] }}</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-0.5">{{ $card['sub'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     FILTER & SEARCH BAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 mb-4">
    <form method="GET" action="{{ route('security.users.index') }}" id="filterForm">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari nama, email, atau IP..."
                    class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all"
                    oninput="debounceSubmit()"
                />
            </div>

            {{-- Status filter pills --}}
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                @foreach(['all' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif', 'blocked' => 'Diblokir', 'deleted' => 'Dihapus'] as $val => $label)
                <button
                    type="button"
                    onclick="setStatus('{{ $val }}')"
                    class="status-btn px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 {{ ($filters['status'] ?? 'all') === $val ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-800 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
                    data-status="{{ $val }}"
                >{{ $label }}</button>
                @endforeach
            </div>
            <input type="hidden" name="status" id="statusInput" value="{{ $filters['status'] ?? 'all' }}"/>

            {{-- Sort --}}
            <select
                name="sort"
                onchange="this.form.submit()"
                class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 appearance-none pr-8 bg-no-repeat"
                style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 8px center;background-size:14px"
            >
                <option value="-created_at"   {{ ($filters['sort'] ?? '-created_at') === '-created_at'   ? 'selected' : '' }}>Terdaftar Terbaru</option>
                <option value="created_at"    {{ ($filters['sort'] ?? '') === 'created_at'    ? 'selected' : '' }}>Terdaftar Lama</option>
                <option value="name"          {{ ($filters['sort'] ?? '') === 'name'          ? 'selected' : '' }}>Nama A → Z</option>
                <option value="-name"         {{ ($filters['sort'] ?? '') === '-name'         ? 'selected' : '' }}>Nama Z → A</option>
                <option value="-last_login_at"{{ ($filters['sort'] ?? '') === '-last_login_at'? 'selected' : '' }}>Login Terbaru</option>
                <option value="block_count"   {{ ($filters['sort'] ?? '') === 'block_count'   ? 'selected' : '' }}>Blokir Terbanyak</option>
            </select>

            {{-- Per page --}}
            <select
                name="per_page"
                onchange="this.form.submit()"
                class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 appearance-none pr-8 bg-no-repeat"
                style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 8px center;background-size:14px"
            >
                @foreach([10, 25, 50, 100] as $pp)
                <option value="{{ $pp }}" {{ (int)($filters['per_page'] ?? 15) === $pp ? 'selected' : '' }}>{{ $pp }} / hal</option>
                @endforeach
            </select>

            {{-- Reset filter --}}
            @if(!empty($filters['search']) || !empty($filters['status']) && $filters['status'] !== 'all')
            <a href="{{ route('security.users.index') }}" class="text-xs text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-400 transition-colors">Reset</a>
            @endif
        </div>
    </form>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     BULK ACTION BAR (muncul saat ada selection)
───────────────────────────────────────────────────────────────────────── --}}
<div id="bulkBar" class="hidden items-center gap-3 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 rounded-xl px-4 py-3 mb-4">
    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span id="bulkCount" class="text-xs font-semibold text-indigo-700 dark:text-indigo-300"></span>
    <div class="flex items-center gap-2 ml-2">
        <button onclick="openBulkBlockModal()" class="px-3 py-1.5 text-xs font-semibold bg-red-100 dark:bg-red-500/15 hover:bg-red-200 dark:hover:bg-red-500/25 text-red-700 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-500/30 transition-all">
            Blokir Semua
        </button>
        <button onclick="bulkAction('unblock')" class="px-3 py-1.5 text-xs font-semibold bg-emerald-100 dark:bg-emerald-500/15 hover:bg-emerald-200 dark:hover:bg-emerald-500/25 text-emerald-700 dark:text-emerald-400 rounded-lg border border-emerald-200 dark:border-emerald-500/30 transition-all">
            Unblokir Semua
        </button>
        <button onclick="bulkAction('delete')" class="px-3 py-1.5 text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-lg border border-slate-200 dark:border-slate-700 transition-all">
            Hapus Semua
        </button>
    </div>
    <button onclick="clearSelection()" class="ml-auto p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     TABLE
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs min-w-[760px]">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 cursor-pointer focus:ring-indigo-500/30"/>
                    </th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px]">Pengguna</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px] hidden md:table-cell">Email</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px] hidden lg:table-cell">IP Terakhir</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px]">Status</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px] hidden xl:table-cell">Login Terakhir</th>
                    <th class="text-left px-3 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px] hidden lg:table-cell">Blokir</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $isBlocked  = $user->is_blocked;
                    $isActive   = $user->is_active && !$isBlocked;
                    $blockCount = $user->user_blocks_count ?? 0;
                @endphp
                <tr
                    class="border-b border-slate-50 dark:border-slate-800/60 last:border-0 hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group"
                    data-user-id="{{ $user->id }}"
                >
                    {{-- Checkbox --}}
                    <td class="px-4 py-3">
                        <input type="checkbox"
                            class="row-checkbox w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 cursor-pointer focus:ring-indigo-500/30"
                            value="{{ $user->id }}"
                            onchange="updateSelection()"
                        />
                    </td>

                    {{-- User --}}
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0 {{ $isBlocked ? 'bg-red-400' : ($isActive ? 'bg-gradient-to-br from-indigo-400 to-purple-500' : 'bg-slate-400') }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-100 leading-none">{{ $user->name }}</p>
                                <p class="font-mono text-[9px] text-slate-400 mt-0.5">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Email --}}
                    <td class="px-3 py-3 hidden md:table-cell">
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-slate-500 dark:text-slate-400 text-[11px]">{{ $user->email }}</span>
                            @if($user->email_verified_at)
                            <span title="Email terverifikasi">
                                <svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </span>
                            @else
                            <span title="Email belum terverifikasi">
                                <svg class="w-3 h-3 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            @endif
                        </div>
                    </td>

                    {{-- IP --}}
                    <td class="px-3 py-3 hidden lg:table-cell">
                        <span class="font-mono text-slate-400 dark:text-slate-500 text-[11px]">{{ $user->last_login_ip ?? '—' }}</span>
                    </td>

                    {{-- Status --}}
                    <td class="px-3 py-3">
                        @if($isBlocked)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span> BLOCKED
                        </span>
                        @elseif($isActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0 animate-pulse"></span> ACTIVE
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 flex-shrink-0"></span> INACTIVE
                        </span>
                        @endif
                    </td>

                    {{-- Login terakhir --}}
                    <td class="px-3 py-3 hidden xl:table-cell">
                        <span class="font-mono text-slate-400 dark:text-slate-600 text-[10px]">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}
                        </span>
                    </td>

                    {{-- Block count --}}
                    <td class="px-3 py-3 hidden lg:table-cell">
                        @if($blockCount > 0)
                        <span class="inline-flex items-center justify-center min-w-[24px] h-5 px-1.5 rounded text-[10px] font-bold tabular-nums {{ $blockCount >= 3 ? 'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400' : 'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400' }}">
                            {{ $blockCount }}×
                        </span>
                        @else
                        <span class="text-slate-300 dark:text-slate-700">—</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">

                            {{-- Detail --}}
                            <button
                                onclick='openDetailModal(@json($user->only(["id","name","email","is_active","last_login_ip","last_login_at","email_verified_at","created_at"])), {{ (int)$isBlocked }}, {{ $blockCount }}, @json($user->activeBlock?->reason))'
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-all"
                                title="Detail"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>

                            {{-- Edit --}}
                            <button
                                onclick='openEditModal(@json($user->only(["id","name","email","is_active","email_verified_at"])))'
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-100 dark:hover:bg-indigo-500/15 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all"
                                title="Edit"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            {{-- Block / Unblock --}}
                            @if($isBlocked)
                            <button
                                onclick="confirmUnblock({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-500/15 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all"
                                title="Unblokir"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                            @else
                            <button
                                onclick="openBlockModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-100 dark:hover:bg-red-500/15 hover:text-red-600 dark:hover:text-red-400 transition-all"
                                title="Blokir"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </button>
                            @endif

                            {{-- Reset Password --}}
                            <button
                                onclick="sendResetPassword({{ $user->id }}, '{{ addslashes($user->email) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-amber-100 dark:hover:bg-amber-500/15 hover:text-amber-600 dark:hover:text-amber-400 transition-all"
                                title="Reset Password"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </button>

                            {{-- Delete --}}
                            <button
                                onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-100 dark:hover:bg-red-500/15 hover:text-red-600 dark:hover:text-red-400 transition-all"
                                title="Hapus"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-sm text-slate-400 dark:text-slate-600">Tidak ada pengguna ditemukan</p>
                            <a href="{{ route('security.users.index') }}" class="text-xs text-indigo-500 hover:underline">Reset filter</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 dark:border-slate-800">
        <p class="text-[11px] font-mono text-slate-400">
            Menampilkan <span class="text-slate-600 dark:text-slate-300">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
            dari <span class="text-slate-600 dark:text-slate-300">{{ $users->total() }}</span> pengguna
        </p>
        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if($users->onFirstPage())
            <span class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
            @else
            <a href="{{ $users->previousPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            @endif

            {{-- Pages --}}
            @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}"
               class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-semibold transition-all {{ $page == $users->currentPage() ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200' }}">
                {{ $page }}
            </a>
            @endforeach

            {{-- Next --}}
            @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @else
            <span class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: CREATE USER
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="createModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl shadow-2xl overflow-hidden modal-panel">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="font-semibold text-slate-800 dark:text-white">Tambah Pengguna Baru</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Isi form berikut untuk membuat akun baru</p>
            </div>
            <button onclick="closeModal('createModal')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
            {{-- Preview avatar --}}
            <div class="flex items-center gap-4 pb-2">
                <div id="createAvatar" class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-xl font-bold text-white flex-shrink-0">?</div>
                <div>
                    <p id="createPreviewName" class="font-semibold text-slate-700 dark:text-slate-200 text-sm">Nama Pengguna</p>
                    <p id="createPreviewEmail" class="text-xs text-slate-400 font-mono mt-0.5">email@domain.com</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input id="createName" type="text" placeholder="John Doe"
                        oninput="updateCreatePreview()"
                        class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all"/>
                    <p class="text-[10px] text-red-500 mt-1 hidden" id="createNameErr">Nama wajib diisi</p>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input id="createEmail" type="email" placeholder="john@example.com"
                        oninput="updateCreatePreview()"
                        class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all font-mono"/>
                    <p class="text-[10px] text-red-500 mt-1 hidden" id="createEmailErr">Email wajib diisi</p>
                </div>
                <div class="col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="createPassword" type="password" placeholder="Min. 8 karakter"
                            class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all pr-10 font-mono"/>
                        <button type="button" onclick="togglePassword('createPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-red-500 mt-1 hidden" id="createPassErr">Password wajib diisi</p>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status Akun</label>
                    <select id="createIsActive" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                        <option value="1">✓ Aktif</option>
                        <option value="0">✗ Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email Verified</label>
                    <select id="createEmailVerified" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                        <option value="1">✓ Terverifikasi</option>
                        <option value="0">✗ Belum</option>
                    </select>
                </div>
            </div>

            <div id="createError" class="hidden flex items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg px-3 py-2.5">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p id="createErrorMsg" class="text-xs text-red-600 dark:text-red-400"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <button onclick="closeModal('createModal')" class="px-4 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-all">Batal</button>
            <button onclick="submitCreate()" id="createSubmitBtn" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all shadow-sm flex items-center gap-2">
                <svg id="createSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Buat Pengguna
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: EDIT USER
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl shadow-2xl overflow-hidden modal-panel">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="font-semibold text-slate-800 dark:text-white">Edit Pengguna</h3>
                <p id="editModalSub" class="text-[11px] text-slate-400 font-mono mt-0.5"></p>
            </div>
            <button onclick="closeModal('editModal')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <input type="hidden" id="editUserId"/>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input id="editName" type="text" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all"/>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input id="editEmail" type="email" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all font-mono"/>
                </div>
                <div class="col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password Baru <span class="text-slate-400">(kosongkan jika tidak diubah)</span></label>
                    <div class="relative">
                        <input id="editPassword" type="password" placeholder="Min. 8 karakter"
                            class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30 transition-all pr-10 font-mono"/>
                        <button type="button" onclick="togglePassword('editPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status Akun</label>
                    <select id="editIsActive" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                        <option value="1">✓ Aktif</option>
                        <option value="0">✗ Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email Verified</label>
                    <select id="editEmailVerified" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-indigo-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                        <option value="1">✓ Terverifikasi</option>
                        <option value="0">✗ Belum</option>
                    </select>
                </div>
            </div>
            <div id="editError" class="hidden flex items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg px-3 py-2.5">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p id="editErrorMsg" class="text-xs text-red-600 dark:text-red-400"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <button onclick="sendResetPasswordFromEdit()" class="px-4 py-2 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-100 dark:hover:bg-amber-500/20 rounded-lg border border-amber-200 dark:border-amber-500/20 transition-all flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Reset Password
            </button>
            <div class="flex items-center gap-2">
                <button onclick="closeModal('editModal')" class="px-4 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-all">Batal</button>
                <button onclick="submitEdit()" id="editSubmitBtn" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all shadow-sm flex items-center gap-2">
                    <svg id="editSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: DETAIL USER
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl shadow-2xl overflow-hidden modal-panel">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-white">Detail Pengguna</h3>
            <button onclick="closeModal('detailModal')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-slate-200 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="flex items-center gap-4">
                <div id="detailAvatar" class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold text-white flex-shrink-0 bg-gradient-to-br from-indigo-400 to-purple-500"></div>
                <div>
                    <p id="detailName" class="font-bold text-slate-800 dark:text-white text-base leading-none"></p>
                    <p id="detailEmail" class="text-xs text-slate-400 font-mono mt-1"></p>
                    <div id="detailStatusBadge" class="mt-2"></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3" id="detailGrid"></div>
            <div id="detailBlockInfo" class="hidden bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl px-4 py-3">
                <p class="text-[10px] text-red-600 dark:text-red-400 font-semibold uppercase tracking-wider mb-1">Riwayat Blokir</p>
                <p id="detailBlockText" class="text-xs text-slate-600 dark:text-slate-300"></p>
                <p id="detailBlockReason" class="text-[10px] text-slate-400 font-mono mt-1"></p>
            </div>
        </div>
        <div class="px-6 pb-5 flex gap-2 border-t border-slate-100 dark:border-slate-800 pt-4">
            <button id="detailEditBtn" onclick="" class="flex-1 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all">Edit User</button>
            <button id="detailBlockBtn" onclick="" class="flex-1 py-2 text-xs font-bold rounded-xl border transition-all"></button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: BLOCK USER
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="blockModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl shadow-2xl overflow-hidden modal-panel">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-white">Blokir Pengguna</h3>
            <p id="blockModalSub" class="text-[11px] text-slate-400 font-mono mt-0.5"></p>
        </div>
        <input type="hidden" id="blockUserId"/>
        <input type="hidden" id="blockUserName"/>
        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Alasan Blokir <span class="text-red-500">*</span></label>
                <select id="blockReason" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                    <option value="">-- Pilih alasan --</option>
                    <option value="Suspicious activity">Aktivitas mencurigakan</option>
                    <option value="Multiple failed logins">Terlalu banyak login gagal</option>
                    <option value="Violation of terms">Pelanggaran ketentuan layanan</option>
                    <option value="Security threat">Ancaman keamanan</option>
                    <option value="Fraudulent activity">Aktivitas penipuan</option>
                    <option value="Manual review">Review manual admin</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Blokir Sampai <span class="text-slate-400">(opsional)</span></label>
                <input id="blockUntil" type="datetime-local"
                    class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all font-mono"/>
                <p class="text-[10px] text-slate-400 mt-1">Kosongkan untuk blokir permanen</p>
            </div>
        </div>
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="closeModal('blockModal')" class="flex-1 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">Batal</button>
            <button onclick="submitBlock()" class="flex-1 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-sm">Konfirmasi Blokir</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: CONFIRM DELETE
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-red-200 dark:border-red-900/40 rounded-2xl shadow-2xl overflow-hidden modal-panel">
        <div class="px-6 pt-6 pb-4 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-base">Hapus Pengguna?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                Akun <strong id="deleteUserName" class="text-slate-700 dark:text-slate-200"></strong> akan dihapus. Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
        <input type="hidden" id="deleteUserId"/>
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="closeModal('deleteModal')" class="flex-1 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">Batal</button>
            <button onclick="submitDelete()" class="flex-1 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-sm">Ya, Hapus</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: BULK BLOCK
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="bulkBlockModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(15,23,42,0.75);backdrop-filter:blur(6px)">
    <div class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 rounded-2xl shadow-2xl overflow-hidden modal-panel">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-semibold text-slate-800 dark:text-white">Blokir Semua yang Dipilih</h3>
            <p id="bulkBlockCount" class="text-[11px] text-slate-400 mt-0.5"></p>
        </div>
        <div class="px-6 py-4">
            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Alasan Blokir <span class="text-red-500">*</span></label>
            <select id="bulkBlockReason" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-red-500 transition-all appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                <option value="">-- Pilih alasan --</option>
                <option value="Suspicious activity">Aktivitas mencurigakan</option>
                <option value="Security threat">Ancaman keamanan</option>
                <option value="Manual review">Review manual admin</option>
            </select>
        </div>
        <div class="px-6 pb-5 flex gap-3">
            <button onclick="closeModal('bulkBlockModal')" class="flex-1 py-2.5 text-xs font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">Batal</button>
            <button onclick="submitBulkBlock()" class="flex-1 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all">Blokir Semua</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TOAST CONTAINER
═══════════════════════════════════════════════════════════════════════════ --}}
<div id="toastContainer" class="fixed bottom-5 right-5 z-[9999] space-y-2 pointer-events-none"></div>

<style>
    @keyframes modalIn { from { opacity:0; transform:translateY(16px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    .modal-panel { animation: modalIn .22s cubic-bezier(.16,1,.3,1); }
    @keyframes toastSlide { from { opacity:0; transform:translateX(24px); } to { opacity:1; transform:translateX(0); } }
    .toast-item { animation: toastSlide .25s cubic-bezier(.16,1,.3,1); }
</style>

<script>
(function () {
    var CSRF = '{{ csrf_token() }}';

    // ── Routes ────────────────────────────────────────────────────────────────
    var ROUTES = {
        store    : '{{ route("security.users.store") }}',
        update   : function(id) { return '{{ url("security/users") }}/' + id; },
        destroy  : function(id) { return '{{ url("security/users") }}/' + id; },
        block    : function(id) { return '{{ url("security/users") }}/' + id + '/block'; },
        unblock  : function(id) { return '{{ url("security/users") }}/' + id + '/unblock'; },
        resetPwd : function(id) { return '{{ url("security/users") }}/' + id + '/reset-password'; },
        bulk     : '{{ route("security.users.bulk") }}',
    };

    // ── Selection ─────────────────────────────────────────────────────────────
    var selectedIds = [];

    window.updateSelection = function () {
        selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(function(c){ return parseInt(c.value); });
        var bar   = document.getElementById('bulkBar');
        var count = document.getElementById('bulkCount');
        var all   = document.getElementById('selectAll');
        var total = document.querySelectorAll('.row-checkbox').length;
        if (selectedIds.length > 0) {
            bar.classList.replace('hidden', 'flex');
            count.textContent = selectedIds.length + ' pengguna dipilih';
        } else {
            bar.classList.replace('flex', 'hidden');
        }
        all.indeterminate = selectedIds.length > 0 && selectedIds.length < total;
        all.checked = selectedIds.length > 0 && selectedIds.length === total;
    };

    window.toggleSelectAll = function (cb) {
        document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked = cb.checked; });
        updateSelection();
    };

    window.clearSelection = function () {
        document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked = false; });
        document.getElementById('selectAll').checked = false;
        selectedIds = [];
        document.getElementById('bulkBar').classList.replace('flex', 'hidden');
    };

    // ── Modal helpers ─────────────────────────────────────────────────────────
    window.closeModal = function (id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    };
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function showError(elId, msg) {
        var el = document.getElementById(elId);
        document.getElementById(elId + 'Msg').textContent = msg;
        el.classList.remove('hidden'); el.classList.add('flex');
    }
    function hideError(elId) {
        var el = document.getElementById(elId);
        el.classList.add('hidden'); el.classList.remove('flex');
    }
    function setLoading(btnId, spinnerId, loading) {
        document.getElementById(btnId).disabled = loading;
        document.getElementById(spinnerId).classList.toggle('hidden', !loading);
    }

    // ── Toast ─────────────────────────────────────────────────────────────────
    window.showToast = function (type, msg) {
        var container = document.getElementById('toastContainer');
        var el = document.createElement('div');
        var styles = {
            success: 'bg-emerald-50 dark:bg-emerald-950 border-emerald-200 dark:border-emerald-700/50 text-emerald-700 dark:text-emerald-300',
            error:   'bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-700/50 text-red-700 dark:text-red-300',
            info:    'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200',
        };
        el.className = 'toast-item pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl border text-xs font-medium shadow-lg max-w-xs ' + (styles[type] || styles.info);
        var icons = { success: '✓', error: '✕', info: '↺' };
        el.innerHTML = '<span class="font-bold text-sm">' + (icons[type]||'•') + '</span><span>' + msg + '</span>';
        container.appendChild(el);
        setTimeout(function () { el.style.opacity='0'; el.style.transform='translateX(20px)'; el.style.transition='all .3s'; setTimeout(function(){ el.remove(); }, 300); }, 3000);
    };

    // ── API helper ────────────────────────────────────────────────────────────
    function api(method, url, data) {
        var opts = {
            method  : method,
            headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        };
        if (data) opts.body = JSON.stringify(data);
        return fetch(url, opts).then(function(r){ return r.json(); });
    }

    // ── Filter form ───────────────────────────────────────────────────────────
    var debounceTimer;
    window.debounceSubmit = function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function(){ document.getElementById('filterForm').submit(); }, 600);
    };
    window.setStatus = function (val) {
        document.getElementById('statusInput').value = val;
        document.querySelectorAll('.status-btn').forEach(function(btn){
            var active = btn.dataset.status === val;
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('dark:bg-slate-700', active);
            btn.classList.toggle('shadow-sm', active);
            btn.classList.toggle('text-slate-800', active);
            btn.classList.toggle('dark:text-white', active);
            btn.classList.toggle('text-slate-500', !active);
            btn.classList.toggle('dark:text-slate-400', !active);
        });
        document.getElementById('filterForm').submit();
    };

    // ── Password toggle ───────────────────────────────────────────────────────
    window.togglePassword = function (inputId, btn) {
        var inp = document.getElementById(inputId);
        inp.type = inp.type === 'password' ? 'text' : 'password';
    };

    // ── CREATE ────────────────────────────────────────────────────────────────
    window.openCreateModal = function () {
        ['createName','createEmail','createPassword'].forEach(function(id){ document.getElementById(id).value = ''; });
        document.getElementById('createIsActive').value = '1';
        document.getElementById('createEmailVerified').value = '1';
        hideError('createError');
        updateCreatePreview();
        openModal('createModal');
    };

    window.updateCreatePreview = function () {
        var name  = document.getElementById('createName').value || 'Nama Pengguna';
        var email = document.getElementById('createEmail').value || 'email@domain.com';
        document.getElementById('createPreviewName').textContent  = name;
        document.getElementById('createPreviewEmail').textContent = email;
        document.getElementById('createAvatar').textContent = name.charAt(0).toUpperCase();
    };

    window.submitCreate = function () {
        var name  = document.getElementById('createName').value.trim();
        var email = document.getElementById('createEmail').value.trim();
        var pass  = document.getElementById('createPassword').value;
        if (!name || !email || !pass) { showError('createError', 'Nama, email, dan password wajib diisi.'); return; }
        hideError('createError');
        setLoading('createSubmitBtn', 'createSpinner', true);
        api('POST', ROUTES.store, {
            name: name, email: email, password: pass,
            is_active: document.getElementById('createIsActive').value === '1',
            email_verified: document.getElementById('createEmailVerified').value === '1',
        }).then(function(res){
            setLoading('createSubmitBtn', 'createSpinner', false);
            if (res.success) { closeModal('createModal'); showToast('success', res.message); setTimeout(function(){ location.reload(); }, 800); }
            else { showError('createError', res.message || 'Gagal membuat pengguna.'); }
        }).catch(function(){ setLoading('createSubmitBtn', 'createSpinner', false); showError('createError', 'Terjadi kesalahan server.'); });
    };

    // ── EDIT ──────────────────────────────────────────────────────────────────
    window.openEditModal = function (user) {
        document.getElementById('editUserId').value  = user.id;
        document.getElementById('editName').value    = user.name;
        document.getElementById('editEmail').value   = user.email;
        document.getElementById('editPassword').value = '';
        document.getElementById('editIsActive').value = user.is_active ? '1' : '0';
        document.getElementById('editEmailVerified').value = user.email_verified_at ? '1' : '0';
        document.getElementById('editModalSub').textContent = '#' + String(user.id).padStart(4,'0') + ' · ' + user.email;
        hideError('editError');
        openModal('editModal');
    };

    window.submitEdit = function () {
        var id    = document.getElementById('editUserId').value;
        var name  = document.getElementById('editName').value.trim();
        var email = document.getElementById('editEmail').value.trim();
        if (!name || !email) { showError('editError', 'Nama dan email wajib diisi.'); return; }
        hideError('editError');
        setLoading('editSubmitBtn', 'editSpinner', true);
        api('PUT', ROUTES.update(id), {
            name: name, email: email,
            password: document.getElementById('editPassword').value || null,
            is_active: document.getElementById('editIsActive').value === '1',
            email_verified: document.getElementById('editEmailVerified').value === '1',
        }).then(function(res){
            setLoading('editSubmitBtn', 'editSpinner', false);
            if (res.success) { closeModal('editModal'); showToast('success', res.message); setTimeout(function(){ location.reload(); }, 800); }
            else { showError('editError', res.message || 'Gagal menyimpan perubahan.'); }
        }).catch(function(){ setLoading('editSubmitBtn', 'editSpinner', false); showError('editError', 'Terjadi kesalahan server.'); });
    };

    window.sendResetPasswordFromEdit = function () {
        var id = document.getElementById('editUserId').value;
        if (!id) return;
        api('POST', ROUTES.resetPwd(id)).then(function(res){ showToast(res.success ? 'success' : 'error', res.message); });
    };

    // ── DETAIL ────────────────────────────────────────────────────────────────
    window.openDetailModal = function (user, isBlocked, blockCount, blockReason) {
        document.getElementById('detailAvatar').textContent = user.name.charAt(0).toUpperCase();
        document.getElementById('detailName').textContent   = user.name;
        document.getElementById('detailEmail').textContent  = user.email;

        var badge = document.getElementById('detailStatusBadge');
        if (isBlocked) {
            badge.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">⊘ BLOCKED</span>';
        } else if (user.is_active) {
            badge.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">● ACTIVE</span>';
        } else {
            badge.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">○ INACTIVE</span>';
        }

        var fields = [
            ['User ID',       '#' + String(user.id).padStart(4,'0')],
            ['Email Verify',  user.email_verified_at ? '✓ Verified' : '✗ Unverified'],
            ['Terdaftar',     user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '—'],
            ['Login Terakhir',user.last_login_at ? new Date(user.last_login_at).toLocaleDateString('id-ID') : 'Belum pernah'],
            ['IP Terakhir',   user.last_login_ip || '—'],
            ['Total Blokir',  blockCount > 0 ? blockCount + '×' : 'Tidak pernah'],
        ];

        var grid = document.getElementById('detailGrid');
        grid.innerHTML = fields.map(function(f){
            return '<div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl px-3 py-2.5"><p class="text-[9px] text-slate-400 uppercase tracking-widest mb-1">' + f[0] + '</p><p class="text-xs font-semibold text-slate-700 dark:text-slate-200 font-mono">' + (f[1] || '—') + '</p></div>';
        }).join('');

        var blockInfo = document.getElementById('detailBlockInfo');
        if (blockCount > 0) {
            blockInfo.classList.remove('hidden');
            document.getElementById('detailBlockText').textContent = 'Pengguna ini telah diblokir sebanyak ' + blockCount + ' kali.';
            document.getElementById('detailBlockReason').textContent = blockReason ? 'Alasan: ' + blockReason : '';
        } else {
            blockInfo.classList.add('hidden');
        }

        document.getElementById('detailEditBtn').onclick = function(){ closeModal('detailModal'); openEditModal(user); };

        var blockBtn = document.getElementById('detailBlockBtn');
        if (isBlocked) {
            blockBtn.textContent = 'Unblokir User';
            blockBtn.className = 'flex-1 py-2 text-xs font-bold rounded-xl border transition-all bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20';
            blockBtn.onclick = function(){ closeModal('detailModal'); confirmUnblock(user.id, user.name); };
        } else {
            blockBtn.textContent = 'Blokir User';
            blockBtn.className = 'flex-1 py-2 text-xs font-bold rounded-xl border transition-all bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20';
            blockBtn.onclick = function(){ closeModal('detailModal'); openBlockModal(user.id, user.name); };
        }

        openModal('detailModal');
    };

    // ── BLOCK ─────────────────────────────────────────────────────────────────
    window.openBlockModal = function (userId, userName) {
        document.getElementById('blockUserId').value = userId;
        document.getElementById('blockUserName').value = userName;
        document.getElementById('blockModalSub').textContent = userName;
        document.getElementById('blockReason').value = '';
        document.getElementById('blockUntil').value = '';
        openModal('blockModal');
    };

    window.submitBlock = function () {
        var id     = document.getElementById('blockUserId').value;
        var reason = document.getElementById('blockReason').value;
        var until  = document.getElementById('blockUntil').value;
        if (!reason) { showToast('error', 'Pilih alasan blokir.'); return; }
        api('POST', ROUTES.block(id), { reason: reason, blocked_until: until || null }).then(function(res){
            closeModal('blockModal');
            showToast(res.success ? 'info' : 'error', res.message);
            if (res.success) setTimeout(function(){ location.reload(); }, 800);
        });
    };

    window.confirmUnblock = function (userId, userName) {
        if (!confirm('Unblokir ' + userName + '?')) return;
        api('POST', ROUTES.unblock(userId)).then(function(res){
            showToast(res.success ? 'success' : 'error', res.message);
            if (res.success) setTimeout(function(){ location.reload(); }, 800);
        });
    };

    // ── DELETE ────────────────────────────────────────────────────────────────
    window.confirmDelete = function (userId, userName) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserName').textContent = userName;
        openModal('deleteModal');
    };

    window.submitDelete = function () {
        var id = document.getElementById('deleteUserId').value;
        api('DELETE', ROUTES.destroy(id)).then(function(res){
            closeModal('deleteModal');
            showToast(res.success ? 'error' : 'error', res.message);
            if (res.success) setTimeout(function(){ location.reload(); }, 800);
        });
    };

    // ── RESET PASSWORD ────────────────────────────────────────────────────────
    window.sendResetPassword = function (userId, email) {
        if (!confirm('Kirim link reset password ke ' + email + '?')) return;
        api('POST', ROUTES.resetPwd(userId)).then(function(res){
            showToast(res.success ? 'success' : 'error', res.message);
        });
    };

    // ── BULK ──────────────────────────────────────────────────────────────────
    window.openBulkBlockModal = function () {
        if (selectedIds.length === 0) return;
        document.getElementById('bulkBlockCount').textContent = selectedIds.length + ' pengguna akan diblokir';
        document.getElementById('bulkBlockReason').value = '';
        openModal('bulkBlockModal');
    };

    window.submitBulkBlock = function () {
        var reason = document.getElementById('bulkBlockReason').value;
        if (!reason) { showToast('error', 'Pilih alasan blokir.'); return; }
        api('POST', ROUTES.bulk, { action: 'block', user_ids: selectedIds, reason: reason }).then(function(res){
            closeModal('bulkBlockModal');
            showToast(res.success ? 'info' : 'error', res.message);
            if (res.success) setTimeout(function(){ location.reload(); }, 800);
        });
    };

    window.bulkAction = function (action) {
        if (selectedIds.length === 0) return;
        var labels = { unblock: 'unblokir', delete: 'hapus' };
        if (!confirm('Apakah yakin ingin ' + (labels[action] || action) + ' ' + selectedIds.length + ' pengguna?')) return;
        api('POST', ROUTES.bulk, { action: action, user_ids: selectedIds }).then(function(res){
            showToast(res.success ? 'success' : 'error', res.message);
            if (res.success) setTimeout(function(){ location.reload(); }, 800);
        });
    };

    // ── Close modal on backdrop click ─────────────────────────────────────────
    ['createModal','editModal','detailModal','blockModal','deleteModal','bulkBlockModal'].forEach(function(id){
        var el = document.getElementById(id);
        el.addEventListener('click', function(e){ if (e.target === el) closeModal(id); });
    });
}());
</script>

@endsection