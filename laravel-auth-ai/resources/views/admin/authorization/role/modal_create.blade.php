<x-app-modal id="createModal" maxWidth="4xl" title="Tambah Role Baru"
    description="Definisikan role baru dan tentukan hak akses yang diizinkan."
    icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/></svg>"
    iconColor="indigo">

    {{-- Override label style dari modal-body khusus di sini --}}
    <style>
        /* Scoped: hanya label yang bukan permission dan bukan toggle */
        #createModal .modal-body .field-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.375rem;
        }
        .dark #createModal .modal-body .field-label { color: #94a3b8; }

        /* Reset label default modal untuk permission items */
        #createModal .modal-body .modal-permission-label,
        #createModal .modal-body .modal-toggle-label {
            display: flex !important;
            text-transform: none !important;
            letter-spacing: normal !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            color: inherit !important;
            margin-bottom: 0 !important;
        }
    </style>

    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Kolom Kiri: Info Dasar --}}
            <div class="flex flex-col gap-4">
                <div>
                    <span class="field-label">Nama Role <span class="text-red-500 normal-case">*</span></span>
                    <input
                        id="createName"
                        type="text"
                        placeholder="mis: Manager Konten"
                        maxlength="50"
                    />
                </div>

                <div>
                    <span class="field-label">Deskripsi</span>
                    <textarea
                        id="createDescription"
                        rows="5"
                        placeholder="Jelaskan fungsi utama role ini..."
                    ></textarea>
                </div>

                {{-- Error Box --}}
                <div
                    id="createError"
                    class="hidden items-start gap-2.5 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 rounded-xl"
                >
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p id="createErrorMsg" class="text-xs text-red-600 dark:text-red-400 font-medium leading-relaxed"></p>
                </div>
            </div>

            {{-- Kolom Kanan: Permissions --}}
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="field-label" style="margin-bottom:0">Permissions</span>
                    <span class="text-xs text-slate-400 dark:text-slate-500">Pilih hak akses</span>
                </div>

                <div class="max-h-[380px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                    @foreach($allPermissions as $group => $perms)
                    <div
                        x-data="{ open: false }"
                        class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/70"
                    >
                        {{-- Accordion Header --}}
                        <div class="w-full flex items-center justify-between px-4 py-2.5">

                            {{-- Kiri: tombol expand (hanya area ini yang toggle collapse) --}}
                            <button
                                type="button"
                                @click.stop="open = !open"
                                class="flex items-center gap-2.5 flex-1 text-left hover:opacity-80 transition-opacity"
                            >
                                <svg
                                    class="w-3.5 h-3.5 text-indigo-500 transition-transform duration-200 shrink-0"
                                    :class="{ 'rotate-90': open }"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-300">
                                    {{ $group }}
                                </span>
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-normal normal-case tracking-normal">
                                    ({{ count($perms) }})
                                </span>
                            </button>

                            {{-- Kanan: toggle "Semua" — terpisah dari button expand --}}
                            <label
                                class="modal-toggle-label flex items-center gap-2 cursor-pointer shrink-0 ml-3"
                                @click.stop
                            >
                                <input
                                    type="checkbox"
                                    data-group="{{ Str::slug($group) }}"
                                    onchange="toggleGroup(this, 'create')"
                                    class="peer sr-only group-toggle-create"
                                />
                                <span class="
                                    relative inline-flex h-4 w-7 rounded-full shrink-0
                                    bg-slate-200 dark:bg-slate-700
                                    peer-checked:bg-indigo-500
                                    transition-colors duration-200
                                ">
                                    <span class="
                                        absolute left-0.5 top-0.5
                                        h-3 w-3 rounded-full bg-white shadow
                                        transition-transform duration-200
                                        peer-checked:translate-x-3
                                    "></span>
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Semua</span>
                            </label>
                        </div>

                        {{-- Permission List --}}
                        <div
                            x-show="open"
                            x-collapse
                            class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 px-2 py-2"
                            @click.stop
                        >
                        <x-permission-grid :permissions="$perms" name="permissions" modalType="create"/>                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('createModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitCreate()" id="createSubmitBtn" class="modal-btn-primary">
            <svg id="createSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Buat Role
        </button>
    </x-slot>
</x-app-modal>