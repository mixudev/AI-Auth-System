@extends('layouts.app-dashboard')

@section('title', 'Access Areas')
@section('page-title', 'Access Areas')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 tracking-tight">Access Areas Management</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola zona akses untuk membatasi ruang lingkup SSO tiap aplikasi.</p>
        </div>
        <button onclick="AppModal.open('createAreaModal')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium rounded-sm transition-all shadow-sm shadow-indigo-500/20">
            <i class="fa-solid fa-plus"></i> Tambah Area
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm overflow-hidden shadow-sm">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-4 font-semibold">Nama Area</th>
                        <th class="px-6 py-4 font-semibold">Slug & Deskripsi</th>
                        <th class="px-6 py-4 font-semibold">Pengguna</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($areas as $area)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-sm bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-500/20 shrink-0">
                                        {{ strtoupper(substr($area->name, 0, 1)) }}
                                    </div>
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $area->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700 dark:text-slate-300 font-mono text-[11px] bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded inline-block mb-1">
                                    {{ $area->slug }}
                                </div>
                                <div class="text-[11px] text-slate-500 truncate max-w-[200px]" title="{{ $area->description }}">
                                    {{ $area->description ?: '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-users text-slate-400"></i>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ number_format($area->users_count) }}</span>
                                    <span class="text-xs text-slate-500">user</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($area->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Assign Button -->
                                    <a href="{{ route('sso.access-areas.edit', $area->id) }}" 
                                       class="h-8 px-3 flex items-center justify-center gap-2 rounded-sm bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:hover:bg-indigo-500/20 transition-colors"
                                       title="Assign Users">
                                        <i class="fa-solid fa-user-plus text-xs"></i> <span class="text-xs font-semibold">Assign</span>
                                    </a>

                                    <!-- Edit Info Button -->
                                    <button type="button" 
                                            onclick="openEditModal({{ $area->id }}, '{{ addslashes($area->name) }}', '{{ addslashes($area->slug) }}', '{{ addslashes($area->description) }}', {{ $area->is_active ? 'true' : 'false' }})"
                                            class="w-8 h-8 flex items-center justify-center rounded-sm bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200 transition-colors"
                                            title="Edit Info Area">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form id="form-delete-{{ $area->id }}" action="{{ route('sso.access-areas.destroy', $area->id) }}" method="POST" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete('form-delete-{{ $area->id }}')"
                                                class="w-8 h-8 flex items-center justify-center rounded-sm bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors"
                                                title="Hapus Area">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mb-3">
                                    <i class="fa-solid fa-layer-group text-xl"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Belum ada Access Area</h3>
                                <p class="text-xs text-slate-500 mt-1">Buat zona akses pertama Anda untuk mengatur izin SSO.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($areas->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30">
                {{ $areas->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Create Access Area -->
<x-app-modal id="createAreaModal" maxWidth="md" title="Tambah Access Area" description="Buat zona akses baru." icon='<i class="fa-solid fa-layer-group text-xl"></i>' iconColor="indigo">
    <form id="createAreaForm" action="{{ route('sso.access-areas.store') }}" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="create_name">Nama Area</label>
                <input type="text" id="create_name" name="name" required placeholder="Contoh: Dosen Area">
            </div>
            <div>
                <label for="create_slug">Slug (Opsional)</label>
                <input type="text" id="create_slug" name="slug" placeholder="Biarkan kosong agar di-generate otomatis">
                <p class="text-[10px] text-slate-500 mt-1">Hanya huruf, angka, strip, tanpa spasi.</p>
            </div>
            <div>
                <label for="create_description">Deskripsi (Opsional)</label>
                <textarea id="create_description" name="description" rows="2" placeholder="Informasi tambahan..."></textarea>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" onclick="AppModal.close('createAreaModal')" class="modal-btn-cancel">Batal</button>
            <button type="submit" form="createAreaForm" class="modal-btn-primary">Buat Area</button>
        </x-slot>
    </form>
</x-app-modal>

<!-- Modal: Edit Access Area -->
<x-app-modal id="editAreaModal" maxWidth="md" title="Edit Info Area" description="Perbarui detail zona akses." icon='<i class="fa-solid fa-pen text-xl"></i>' iconColor="emerald">
    <form id="editAreaForm" method="POST">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div>
                <label for="edit_name">Nama Area</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div>
                <label for="edit_slug">Slug</label>
                <input type="text" id="edit_slug" name="slug">
            </div>
            <div>
                <label for="edit_description">Deskripsi (Opsional)</label>
                <textarea id="edit_description" name="description" rows="2"></textarea>
            </div>
            <label class="flex items-center gap-2 cursor-pointer mt-2">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Status Area Aktif</span>
            </label>
        </div>
        <x-slot name="footer">
            <button type="button" onclick="AppModal.close('editAreaModal')" class="modal-btn-cancel">Batal</button>
            <button type="submit" form="editAreaForm" class="modal-btn-primary !bg-emerald-600 !border-emerald-700 hover:!bg-emerald-700">Simpan Perubahan</button>
        </x-slot>
    </form>
</x-app-modal>

<script>
    function openEditModal(id, name, slug, description, isActive) {
        document.getElementById('editAreaForm').action = `/dashboard/sso/access-areas/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_slug').value = slug;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_is_active').checked = isActive;
        
        AppModal.open('editAreaModal');
    }

    function confirmDelete(formId) {
        AppPopup.confirm({
            title: 'Hapus Access Area?',
            description: 'Tindakan ini permanen. Semua pengguna yang terhubung dengan area ini akan kehilangan aksesnya.',
            confirmText: 'Ya, Hapus Area',
            onConfirm: () => document.getElementById(formId).submit()
        });
    }

    // Auto-generate Slug for Create Area
    document.addEventListener('DOMContentLoaded', () => {
        const createName = document.getElementById('create_name');
        const createSlug = document.getElementById('create_slug');
        let slugManuallyEdited = false;

        if (createName && createSlug) {
            createName.addEventListener('input', () => {
                if (!slugManuallyEdited) {
                    createSlug.value = createName.value
                        .toLowerCase()
                        .trim()
                        .replace(/[^\s\w-]/g, '') // Hapus karakter non-alphanumeric/space/hyphen
                        .replace(/[\s_-]+/g, '-') // Ganti spasi/underscore dengan hyphen
                        .replace(/^-+|-+$/g, ''); // Hapus hyphen di awal/akhir
                }
            });

            createSlug.addEventListener('input', () => {
                slugManuallyEdited = true;
                // Jika input slug dikosongkan, kembalikan mode ke auto-generate
                if (createSlug.value === '') {
                    slugManuallyEdited = false;
                }
            });
        }
    });
</script>
@endsection
