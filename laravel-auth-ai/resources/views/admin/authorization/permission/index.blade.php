@extends('layouts.app-dashboard')

@section('title', 'Permission Management')
@section('page-title', 'Permissions')
@section('page-sub', 'Kontrol detail izin akses untuk setiap fitur dalam sistem.')

@section('content')

{{-- ─────────────────────────────────────────────────────────────────────────
     TOOLBAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Manajemen Permission</h1>
        <p class="text-xs text-slate-400 mt-0.5">Kelola butiran izin (granular permissions) yang dapat diberikan ke role.</p>
    </div>
    @if(auth()->user()->hasPermission('permissions.create'))
    <x-ui.primary-button onclick="openCreateModal()">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Permission
    </x-ui.primary-button>
    @endif
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     STAT CARDS
───────────────────────────────────────────────────────────────────────── --}}
@php
$statCards = [
    ['label' => 'Total Permission', 'val' => $stats['total'],  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'emerald', 'sub' => 'seluruh izin'],
    ['label' => 'Grup Fitur', 'val' => $stats['groups'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4"/>', 'color' => 'sky', 'sub' => 'modul terdaftar'],
];
$colorMap = [
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10',   'ic' => 'text-emerald-600 dark:text-emerald-400',   'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
    'sky'  => ['bg' => 'bg-sky-50 dark:bg-sky-500/10',     'ic' => 'text-sky-600 dark:text-sky-400',     'ring' => 'ring-sky-100 dark:ring-sky-500/20'],
];
@endphp

<div class="w-full grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach($statCards as $card)
    @php $c = $colorMap[$card['color']]; @endphp

    <div
        class="
            flex items-center justify-between
            rounded-lg border border-slate-200 dark:border-slate-800
            bg-white dark:bg-slate-900
            px-4 py-3
            hover:bg-slate-50 dark:hover:bg-slate-800
            transition
        "
    >
        <!-- Left -->
        <div class="space-y-0.5">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                {{ $card['label'] }}
            </p>
            <p class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ $card['val'] }}
            </p>
        </div>

        <!-- Right icon -->
        <div class="w-9 h-9 rounded-md {{ $c['bg'] }} ring-1 {{ $c['ring'] }}
                    flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 {{ $c['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $card['icon'] !!}
            </svg>
        </div>
    </div>
    @endforeach
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     FILTER & SEARCH
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[240px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="text"
                name="search"
                placeholder="Cari permission atau deskripsi..."
                value="{{ $filters['search'] ?? '' }}"
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-all"
            />
        </div>

        <select name="group" class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none">
            <option value="">Semua Grup</option>
            @foreach($groups as $grp)
            <option value="{{ $grp }}" {{ ($filters['group'] ?? '') === $grp ? 'selected' : '' }}>{{ ucfirst($grp) }}</option>
            @endforeach
        </select>
        
        <select name="sort" class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none">
            <option value="name" {{ ($filters['sort'] ?? 'name') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
            <option value="recent" {{ ($filters['sort'] ?? 'name') === 'recent' ? 'selected' : '' }}>Terbaru</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-lg hover:bg-indigo-100 transition-colors">
            Filter
        </button>

        @if(!empty($filters['search']) || !empty($filters['group']))
        <a href="{{ route('dashboard.permissions.index') }}" class="text-xs text-slate-400 hover:text-indigo-500 transition-colors">Reset</a>
        @endif
    </form>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     TABLE
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-5 py-4 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Permission Name</th>
                    <th class="px-5 py-4 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Grup</th>
                    <th class="px-5 py-4 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Deskripsi</th>
                    <th class="px-5 py-4 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Dipakai Role</th>
                    <th class="px-5 py-4 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($permissions as $permission)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $permission->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-tight bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            {{ $permission->group }}
                        </span>
                    </td>
                    <td class="px-5 py-4 max-w-xs">
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">
                            {{ $permission->description ?? '-' }}
                        </p>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold border border-indigo-100 dark:border-indigo-500/20">
                            {{ $permission->roles()->count() }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            @if(auth()->user()->hasPermission('permissions.view'))
                            <button
                                onclick="openEditModal({{ json_encode($permission) }})"
                                class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition-colors"
                                title="Edit"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endif
                            
                            @if(auth()->user()->hasPermission('permissions.delete'))
                            <button
                                onclick="confirmDelete({{ $permission->id }}, '{{ $permission->name }}')"
                                class="p-1.5 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 transition-colors"
                                title="Hapus"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3H4v2h16V7h-3z"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-slate-400 text-sm">
                        Tidak ada permission ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     PAGINATION
───────────────────────────────────────────────────────────────────────── --}}
<div class="mt-6">
    {{ $permissions->links() }}
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════════════════════════ --}}
@include('authorization::permission.modal_create')
@include('authorization::permission.modal_edit')

<script>
(function() {
    const CSRF = '{{ csrf_token() }}';
    const ROUTES = {
        store: '{{ route("dashboard.permissions.store") }}',
        update: (id) => `{{ url("dashboard/authorization/permissions") }}/${id}`,
        destroy: (id) => `{{ url("dashboard/authorization/permissions") }}/${id}`
    };

    window.openCreateModal = function() {
        document.getElementById('createName').value = '';
        document.getElementById('createDescription').value = '';
        document.getElementById('createGroup').value = '';
        hideError('createError');
        AppModal.open('createModal');
    };

    window.submitCreate = function() {
        const name = document.getElementById('createName').value.trim();
        const description = document.getElementById('createDescription').value.trim();
        const group = document.getElementById('createGroup').value.trim();

        if (!name || !group) { showError('createError', 'Nama dan Grup wajib diisi.'); return; }
        
        hideError('createError');
        setLoading('createSubmitBtn', 'createSpinner', true);

        fetch(ROUTES.store, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name, description, group })
        })
        .then(r => r.json())
        .then(res => {
            setLoading('createSubmitBtn', 'createSpinner', false);
            if (res.success) {
                AppModal.close('createModal');
                showToast('success', res.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showError('createError', res.message || 'Gagal membuat permission.');
            }
        })
        .catch(() => {
            setLoading('createSubmitBtn', 'createSpinner', false);
            showError('createError', 'Terjadi kesalahan server.');
        });
    };

    window.openEditModal = function(perm) {
        document.getElementById('editPermId').value = perm.id;
        document.getElementById('editName').value = perm.name;
        document.getElementById('editDescription').value = perm.description || '';
        document.getElementById('editGroup').value = perm.group;
        document.getElementById('editModalSub').textContent = `#${perm.slug}`;
        
        hideError('editError');
        AppModal.open('editModal');
    };

    window.submitEdit = function() {
        const id = document.getElementById('editPermId').value;
        const description = document.getElementById('editDescription').value.trim();
        const group = document.getElementById('editGroup').value.trim();

        if (!group) { showError('editError', 'Grup wajib diisi.'); return; }

        hideError('editError');
        setLoading('editSubmitBtn', 'editSpinner', true);

        fetch(ROUTES.update(id), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ description, group })
        })
        .then(r => r.json())
        .then(res => {
            setLoading('editSubmitBtn', 'editSpinner', false);
            if (res.success) {
                AppModal.close('editModal');
                showToast('success', res.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showError('editError', res.message || 'Gagal memperbarui permission.');
            }
        })
        .catch(() => {
            setLoading('editSubmitBtn', 'editSpinner', false);
            showError('editError', 'Terjadi kesalahan server.');
        });
    };

    window.confirmDelete = function(id, name) {
        AppPopup.confirm({
            title: 'Hapus Permission?',
            description: `Permission "${name}" akan dihapus. Role yang memiliki izin ini akan kehilangan akses tersebut.`,
            confirmText: 'Ya, Hapus',
            onConfirm: () => {
                fetch(ROUTES.destroy(id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showToast('success', res.message);
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast('error', res.message);
                    }
                })
                .catch(() => showToast('error', 'Terjadi kesalahan server.'));
            }
        });
    };

    function showError(id, msg) {
        const el = document.getElementById(id);
        document.getElementById(id + 'Msg').textContent = msg;
        el.classList.remove('hidden');
    }
    function hideError(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function setLoading(btnId, spinnerId, loading) {
        document.getElementById(btnId).disabled = loading;
        document.getElementById(spinnerId).classList.toggle('hidden', !loading);
    }
})();
</script>

@endsection
