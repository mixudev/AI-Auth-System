@extends('layouts.app-dashboard')

@section('title', 'Assign Users - ' . $accessArea->name)
@section('page-title', 'Assign Users')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    <!-- Header & Back Button -->
    <div class="flex items-center gap-4">
        <a href="{{ route('sso.access-areas.index') }}" class="w-10 h-10 flex items-center justify-center rounded-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-indigo-600 transition-all shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Manajemen Akses Pengguna</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Atur siapa saja yang memiliki akses ke area <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $accessArea->name }}</span>.</p>
        </div>
    </div>

    <!-- Section 1: Detail Access Area (Hero Card) -->
    <div class="relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm shadow-sm">
        <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
            <i class="fa-solid fa-layer-group text-8xl text-indigo-500"></i>
        </div>
        <div class="p-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-circle-info text-[10px]"></i> Informasi Area
                    </div>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ $accessArea->name }}</h1>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed max-w-xl">
                        {{ $accessArea->description ?: 'Tidak ada deskripsi untuk area ini. Gunakan area ini untuk mengelompokkan akses spesifik bagi pengguna tertentu di jaringan SSO.' }}
                    </p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-1 gap-6">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Slug Identifier</span>
                        <code class="text-sm font-mono text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-500/20">{{ $accessArea->slug }}</code>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Status Keaktifan</span>
                        @if($accessArea->is_active)
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                                <i class="fa-solid fa-circle-check"></i> Aktif & Tersedia
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-slate-400 font-bold">
                                <i class="fa-solid fa-circle-xmark"></i> Nonaktif
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col justify-center items-center md:items-end">
                    <div class="text-center md:text-right">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Terdaftar</span>
                        <div class="text-5xl font-black text-slate-900 dark:text-white">{{ number_format($assignedUsers->total()) }}</div>
                        <span class="text-sm text-slate-500 font-medium">Pengguna Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Available Users Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm shadow-sm overflow-hidden border-t-4 border-t-indigo-500">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Pengguna Tersedia</h3>
                <p class="text-xs text-slate-500">Daftar pengguna yang belum memiliki akses ke area ini.</p>
            </div>
            
            <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="assign_page" value="{{ request('assign_page') }}">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search_avail" value="{{ request('search_avail') }}" placeholder="Cari nama/email..." 
                           class="pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-sm text-sm focus:ring-2 focus:ring-indigo-500 w-64">
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-sm text-sm font-bold shadow-sm hover:bg-indigo-700 transition-all">Cari</button>
            </form>
        </div>

        <form action="{{ route('sso.access-areas.assign-user', $accessArea->id) }}" method="POST" id="assignForm">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                            <th class="px-8 py-4 w-10">
                                <input type="checkbox" id="selectAllAvail" class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </th>
                            <th class="px-6 py-4">Nama Pengguna</th>
                            <th class="px-6 py-4">Email Address</th>
                            <th class="px-6 py-4 text-right">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($availableUsers as $user)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-500/5 transition-colors group">
                            <td class="px-8 py-4">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="avail-checkbox rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" onclick="quickAssign({{ $user->id }})" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase tracking-tighter">Assign</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-users-slash text-3xl mb-3 block opacity-20"></i>
                                Tidak ada pengguna tersedia atau semua sudah di-assign.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-800/20 flex flex-col md:flex-row items-center justify-between gap-6">
                <button type="submit" id="btnAssignBulk" disabled class="bg-indigo-600 text-white px-6 py-3 rounded-sm text-sm font-black shadow-lg shadow-indigo-500/20 disabled:opacity-30 disabled:grayscale transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Tambahkan yang Dipilih (<span id="countAvail">0</span>)
                </button>
                
                <div class="custom-pagination">
                    {{ $availableUsers->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </form>
    </div>

    <!-- Section 3: Assigned Users Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm shadow-sm overflow-hidden border-t-4 border-t-emerald-500">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Pengguna Terdaftar</h3>
                <p class="text-xs text-slate-500">Daftar pengguna yang saat ini memiliki akses ke area ini.</p>
            </div>
            
            <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="avail_page" value="{{ request('avail_page') }}">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search_assign" value="{{ request('search_assign') }}" placeholder="Cari nama/email..." 
                           class="pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-sm text-sm focus:ring-2 focus:ring-emerald-500 w-64">
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-sm text-sm font-bold shadow-sm hover:bg-emerald-700 transition-all">Cari</button>
            </form>
        </div>

        <form action="{{ route('sso.access-areas.revoke-user', $accessArea->id) }}" method="POST" id="revokeForm">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                            <th class="px-8 py-4 w-10">
                                <input type="checkbox" id="selectAllAssign" class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            </th>
                            <th class="px-6 py-4">Nama Pengguna</th>
                            <th class="px-6 py-4">Email Address</th>
                            <th class="px-6 py-4 text-right">Bahaya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($assignedUsers as $user)
                        <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-500/5 transition-colors group">
                            <td class="px-8 py-4">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="assign-checkbox rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-xs font-bold text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" onclick="quickRevoke({{ $user->id }})" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-tighter">Cabut Akses</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-users-viewfinder text-3xl mb-3 block opacity-20"></i>
                                Belum ada pengguna yang terdaftar di area ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-800/20 flex flex-col md:flex-row items-center justify-between gap-6">
                <button type="submit" id="btnRevokeBulk" disabled class="bg-red-500 text-white px-6 py-3 rounded-sm text-sm font-black shadow-lg shadow-red-500/20 hover:bg-red-600 disabled:opacity-30 disabled:grayscale transition-all flex items-center gap-2">
                    <i class="fa-solid fa-trash-can"></i> Cabut yang Dipilih (<span id="countAssign">0</span>)
                </button>
                
                <div class="custom-pagination">
                    {{ $assignedUsers->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Logic Available Users ---
        const selectAllAvail = document.getElementById('selectAllAvail');
        const availCheckboxes = document.querySelectorAll('.avail-checkbox');
        const btnAssignBulk = document.getElementById('btnAssignBulk');
        const countAvail = document.getElementById('countAvail');

        const updateAvailState = () => {
            const checkedCount = [...availCheckboxes].filter(c => c.checked).length;
            countAvail.textContent = checkedCount;
            btnAssignBulk.disabled = checkedCount === 0;
            selectAllAvail.checked = checkedCount === availCheckboxes.length && availCheckboxes.length > 0;
        };

        if(selectAllAvail) {
            selectAllAvail.addEventListener('change', (e) => {
                availCheckboxes.forEach(c => c.checked = e.target.checked);
                updateAvailState();
            });
        }

        availCheckboxes.forEach(c => c.addEventListener('change', updateAvailState));

        // --- Logic Assigned Users ---
        const selectAllAssign = document.getElementById('selectAllAssign');
        const assignCheckboxes = document.querySelectorAll('.assign-checkbox');
        const btnRevokeBulk = document.getElementById('btnRevokeBulk');
        const countAssign = document.getElementById('countAssign');

        const updateAssignState = () => {
            const checkedCount = [...assignCheckboxes].filter(c => c.checked).length;
            countAssign.textContent = checkedCount;
            btnRevokeBulk.disabled = checkedCount === 0;
            selectAllAssign.checked = checkedCount === assignCheckboxes.length && assignCheckboxes.length > 0;
        };

        if(selectAllAssign) {
            selectAllAssign.addEventListener('change', (e) => {
                assignCheckboxes.forEach(c => c.checked = e.target.checked);
                updateAssignState();
            });
        }

        assignCheckboxes.forEach(c => c.addEventListener('change', updateAssignState));
    });

    // Helper functions for quick actions
    function quickAssign(userId) {
        AppPopup.confirm({
            title: 'Assign Pengguna?',
            description: 'Berikan akses area ini ke pengguna terpilih secara instan.',
            confirmText: 'Ya, Berikan Akses',
            onConfirm: () => {
                const form = document.getElementById('assignForm');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userId;
                form.innerHTML = ''; // Clear other checkboxes for quick single assign
                form.appendChild(input);
                // Re-add CSRF
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                form.submit();
            }
        });
    }

    function quickRevoke(userId) {
        AppPopup.confirm({
            title: 'Cabut Akses?',
            description: 'Pengguna ini tidak akan bisa lagi mengakses area ini.',
            confirmText: 'Ya, Cabut Akses',
            onConfirm: () => {
                const form = document.getElementById('revokeForm');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userId;
                form.innerHTML = '';
                form.appendChild(input);
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                form.submit();
            }
        });
    }
</script>

<style>
    /* Pagination Overrides for multiple tables */
    .custom-pagination nav {
        @apply shadow-none border-none p-0;
    }
</style>
@endsection
