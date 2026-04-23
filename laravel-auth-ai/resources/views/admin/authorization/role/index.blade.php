@extends('layouts.app-dashboard')

@section('title', 'Role Management')
@section('page-title', 'Roles')
@section('page-sub', 'Kelola role dan hak akses pengguna sistem dengan standar keamanan tinggi.')

@section('content')

{{-- ─────────────────────────────────────────────────────────────────────────
     TOOLBAR
───────────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Manajemen Role</h1>
        <p class="text-xs text-slate-400 mt-0.5">Definisikan level akses dan izin untuk setiap grup pengguna.</p>
    </div>
    @if(auth()->user()->hasPermission('roles.create'))
    <x-ui.primary-button onclick="openCreateModal()">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Role
    </x-ui.primary-button>
    @endif
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     STAT CARDS
───────────────────────────────────────────────────────────────────────── --}}
@php
$statCards = [
    ['label' => 'Total Role',  'val' => $stats['total'],  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>', 'color' => 'indigo', 'sub' => 'seluruh role'],
    ['label' => 'System Role', 'val' => $stats['system'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/>', 'color' => 'blue', 'sub' => 'bawaan sistem'],
    ['label' => 'Custom Role',  'val' => $stats['custom'], 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>', 'color' => 'emerald', 'sub' => 'buatan admin'],
];
$colorMap = [
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-500/10',   'ic' => 'text-indigo-600 dark:text-indigo-400',   'ring' => 'ring-indigo-100 dark:ring-indigo-500/20'],
    'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-500/10',       'ic' => 'text-blue-600 dark:text-blue-400',       'ring' => 'ring-blue-100 dark:ring-blue-500/20'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'ic' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-500/20'],
];
@endphp

<div class="w-full grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
    @foreach($statCards as $card)
    @php $c = $colorMap[$card['color']]; @endphp

    <div class="group relative flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-5 py-4 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-sm transition-all duration-200 overflow-hidden">

        {{-- Background glow subtle --}}
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 {{ $c['bg'] }} [mask-image:radial-gradient(ellipse_at_top_right,black_0%,transparent_70%)]"></div>

        {{-- Left --}}
        <div class="relative space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                {{ $card['label'] }}
            </p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 tabular-nums leading-none">
                {{ $card['val'] }}
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-600 font-medium">
                {{ $card['sub'] }}
            </p>
        </div>

        {{-- Right icon --}}
        <div class="relative w-9 h-9 rounded-lg {{ $c['bg'] }} flex items-center justify-center shrink-0 ring-1 {{ $c['ring'] }}">
            <svg class="w-4 h-4 {{ $c['ic'] }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
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
                placeholder="Cari nama role atau deskripsi..."
                value="{{ $filters['search'] ?? '' }}"
                class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-all"
            />
        </div>
        
        <select name="sort" class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none">
            <option value="name" {{ ($filters['sort'] ?? 'name') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
            <option value="recent" {{ ($filters['sort'] ?? 'name') === 'recent' ? 'selected' : '' }}>Terbaru</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-xs rounded-lg hover:bg-indigo-100 transition-colors">
            Terapkan Filter
        </button>

        @if(!empty($filters['search']))
        <a href="{{ route('dashboard.roles.index') }}" class="text-xs text-slate-400 hover:text-indigo-500 transition-colors">Reset</a>
        @endif
    </form>
</div>

{{-- ─────────────────────────────────────────────────────────────────────────
     ROLES TABLE
───────────────────────────────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <th class="px-5 py-4 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Identitas Role</th>
                    <th class="px-5 py-4 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Deskripsi</th>
                    <th class="px-5 py-4 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Akses & User</th>
                    <th class="px-5 py-4 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($roles as $role)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @php
                                $isSystem = in_array($role->slug, ['super-admin', 'admin', 'user', 'security-officer']);
                                $colors = ['super-admin' => 'red', 'admin' => 'indigo', 'security-officer' => 'amber', 'user' => 'slate'];
                                $color = $colors[$role->slug] ?? 'indigo';
                                $cMap = [
                                    'red' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
                                    'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20',
                                    'amber' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                    'slate' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                ];
                            @endphp
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ explode(' ', $cMap[$color])[0] }} border {{ explode(' ', $cMap[$color])[2] }}">
                                <span class="font-bold text-xs">{{ strtoupper(substr($role->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $role->name }}</span>
                                    @if($isSystem)
                                    <svg class="w-3 h-3 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.9L9.03 9.03a2 2 0 001.94 0L17.834 4.9A2 2 0 0016 1.5H4a2 2 0 00-1.834 3.4zM18 10a2 2 0 01-2 2H4a2 2 0 01-2-2V7.166l6.03 3.618a4 4 0 003.94 0L18 7.166V10z" clip-rule="evenodd"/></svg>
                                    @endif
                                </div>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $role->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 max-w-xs">
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                            {{ $role->description ?? 'Tidak ada deskripsi tersedia.' }}
                        </p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-4">
                            <div class="text-center">
                                <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ $role->permissions()->count() }}</span>
                                <span class="text-[9px] uppercase font-semibold text-slate-400 tracking-tight">Izin</span>
                            </div>
                            <div class="w-px h-4 bg-slate-100 dark:bg-slate-800"></div>
                            <div class="text-center">
                                <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ $role->users()->count() }}</span>
                                <span class="text-[9px] uppercase font-semibold text-slate-400 tracking-tight">User</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            @if(auth()->user()->hasPermission('roles.view'))
                            <button
                                onclick="openEditModal({{ json_encode($role) }}, {{ json_encode($role->permissions->pluck('id')) }})"
                                class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition-colors"
                                title="Edit Role"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            @endif
                            
                            @if(!$isSystem && auth()->user()->hasPermission('roles.delete'))
                            <button
                                onclick="confirmDelete({{ $role->id }}, '{{ $role->name }}')"
                                class="p-1.5 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 transition-colors"
                                title="Hapus Role"
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
                    <td colspan="4" class="px-5 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-slate-400 text-sm">Tidak ada role ditemukan</p>
                        </div>
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
    {{ $roles->links() }}
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════════════════════════ --}}
@include('authorization::role.modal_create')
@include('authorization::role.modal_edit')

<script>
(function() {
    const CSRF = '{{ csrf_token() }}';
    const ROUTES = {
        store: '{{ route("dashboard.roles.store") }}',
        update: (id) => `{{ url("dashboard/authorization/roles") }}/${id}`,
        destroy: (id) => `{{ url("dashboard/authorization/roles") }}/${id}`
    };

    // ── Create Role ──
    window.openCreateModal = function() {
        document.getElementById('createName').value = '';
        document.getElementById('createDescription').value = '';
        document.querySelectorAll('.perm-checkbox-create').forEach(cb => cb.checked = false);
        document.querySelectorAll('.group-toggle-create').forEach(cb => {
            cb.checked = false;
            cb.indeterminate = false;
        });
        hideError('createError');
        AppModal.open('createModal');
    };

    window.submitCreate = function() {
        const name = document.getElementById('createName').value.trim();
        const description = document.getElementById('createDescription').value.trim();
        const permissions = Array.from(document.querySelectorAll('.perm-checkbox-create:checked')).map(cb => cb.value);

        if (!name) { showError('createError', 'Nama role wajib diisi.'); return; }

        hideError('createError');
        setLoading('createSubmitBtn', 'createSpinner', true);

        fetch(ROUTES.store, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name, description, permissions })
        })
        .then(r => r.json())
        .then(res => {
            setLoading('createSubmitBtn', 'createSpinner', false);
            if (res.success) {
                AppModal.close('createModal');
                showToast('success', res.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showError('createError', res.message || 'Gagal membuat role.');
            }
        })
        .catch(() => {
            setLoading('createSubmitBtn', 'createSpinner', false);
            showError('createError', 'Terjadi kesalahan server.');
        });
    };

    // ── Edit Role ──
    window.openEditModal = function(role, rolePermissions) {
        document.getElementById('editRoleId').value = role.id;
        document.getElementById('editName').value = role.name;
        document.getElementById('editDescription').value = role.description || '';
        document.getElementById('editModalSub').textContent = `#${role.slug}`;

        document.querySelectorAll('.perm-checkbox-edit').forEach(cb => {
            cb.checked = rolePermissions.includes(parseInt(cb.value));
        });

        // Sync semua group toggle edit
        document.querySelectorAll('.group-toggle-edit').forEach(toggle => {
            const group = toggle.dataset.group;
            const allInGroup = document.querySelectorAll(`.perm-group-edit-${group}`);
            const allChecked  = Array.from(allInGroup).every(cb => cb.checked);
            const someChecked = Array.from(allInGroup).some(cb => cb.checked);
            toggle.checked       = allChecked;
            toggle.indeterminate = !allChecked && someChecked;
        });

        hideError('editError');
        AppModal.open('editModal');
    };

    window.submitEdit = function() {
        const id = document.getElementById('editRoleId').value;
        const name = document.getElementById('editName').value.trim();
        const description = document.getElementById('editDescription').value.trim();
        const permissions = Array.from(document.querySelectorAll('.perm-checkbox-edit:checked')).map(cb => cb.value);

        if (!name) { showError('editError', 'Nama role wajib diisi.'); return; }

        hideError('editError');
        setLoading('editSubmitBtn', 'editSpinner', true);

        fetch(ROUTES.update(id), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ name, description, permissions })
        })
        .then(r => r.json())
        .then(res => {
            setLoading('editSubmitBtn', 'editSpinner', false);
            if (res.success) {
                AppModal.close('editModal');
                showToast('success', res.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showError('editError', res.message || 'Gagal memperbarui role.');
            }
        })
        .catch(() => {
            setLoading('editSubmitBtn', 'editSpinner', false);
            showError('editError', 'Terjadi kesalahan server.');
        });
    };

    // ── Delete Role ──
    window.confirmDelete = function(id, name) {
        AppPopup.confirm({
            title: 'Hapus Role?',
            description: `Role "${name}" akan dihapus permanen. Aksi ini tidak dapat dibatalkan.`,
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

    // ── Toggle Semua Checkbox dalam Group ──
    window.toggleGroup = function(toggle, type) {
        const group = toggle.dataset.group;
        const checkboxes = document.querySelectorAll(`.perm-group-${type}-${group}`);
        checkboxes.forEach(cb => cb.checked = toggle.checked);
        toggle.indeterminate = false;
    };

    // ── Auto-sync Group Toggle saat Checkbox Individual Diubah ──
    document.addEventListener('change', function(e) {
        const cb = e.target;

        // Hanya proses checkbox permission
        if (!cb.classList.contains('perm-checkbox-create') && !cb.classList.contains('perm-checkbox-edit')) return;

        // Ambil type: 'create' atau 'edit'
        const type = cb.classList.contains('perm-checkbox-create') ? 'create' : 'edit';

        // Ambil nama group dari class perm-group-{type}-{group}
        const groupClass = Array.from(cb.classList).find(c => c.startsWith(`perm-group-${type}-`));
        if (!groupClass) return;

        // Ekstrak nama group (bisa mengandung dash, misal: "user-management")
        const group = groupClass.replace(`perm-group-${type}-`, '');

        const allInGroup  = document.querySelectorAll(`.perm-group-${type}-${group}`);
        const allChecked  = Array.from(allInGroup).every(c => c.checked);
        const someChecked = Array.from(allInGroup).some(c => c.checked);

        const groupToggle = document.querySelector(`.group-toggle-${type}[data-group="${group}"]`);
        if (!groupToggle) return;

        groupToggle.checked       = allChecked;
        groupToggle.indeterminate = !allChecked && someChecked;
    });

    // ── Helpers ──
    function showError(id, msg) {
        const el = document.getElementById(id);
        document.getElementById(id + 'Msg').textContent = msg;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function hideError(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
    function setLoading(btnId, spinnerId, loading) {
        document.getElementById(btnId).disabled = loading;
        document.getElementById(spinnerId).classList.toggle('hidden', !loading);
    }

})();
</script>

@endsection
