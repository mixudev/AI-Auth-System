<x-app-modal id="createModal" maxWidth="lg" title="Tambah Pengguna Baru" description="Isi form berikut untuk membuat akun baru" icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'/></svg>" iconColor="indigo">
    <div class="space-y-4">
        {{-- Preview avatar --}}
        <div class="flex items-center gap-4 pb-2 border-b border-gray-100 dark:border-white/10 mb-4">
            <div id="createAvatar" class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-xl font-bold text-white flex-shrink-0">?</div>
            <div>
                <p id="createPreviewName" class="font-semibold text-gray-900 dark:text-slate-100 text-sm">Nama Pengguna</p>
                <p id="createPreviewEmail" class="text-xs text-gray-500 dark:text-slate-400 font-mono mt-0.5">email@domain.com</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <label>Nama Lengkap <span class="text-red-500">*</span></label>
                <input id="createName" type="text" placeholder="John Doe" oninput="updateCreatePreview()"/>
                <p class="text-[10px] text-red-500 mt-1 hidden" id="createNameErr">Nama wajib diisi</p>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label>Email <span class="text-red-500">*</span></label>
                <input id="createEmail" type="email" placeholder="john@example.com" oninput="updateCreatePreview()" class="font-mono"/>
                <p class="text-[10px] text-red-500 mt-1 hidden" id="createEmailErr">Email wajib diisi</p>
            </div>
            <div class="col-span-2">
                <label>Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input id="createPassword" type="password" placeholder="Min. 8 karakter" class="pr-10 font-mono"/>
                    <button type="button" onclick="togglePassword('createPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p class="text-[10px] text-red-500 mt-1 hidden" id="createPassErr">Password wajib diisi</p>
            </div>
            <div class="col-span-2">
                <label>Role / Hak Akses <span class="text-red-500">*</span></label>
                @include('identity::users.partials.role_select', ['id' => 'createRoles', 'roles' => $roles])
                <p class="text-[10px] text-gray-400 mt-1">Anda dapat memilih satu atau lebih role untuk pengguna ini.</p>
            </div>
            <div class="col-span-2">
                <label>Status Akun</label>
                <select id="createIsActive" class="appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>

        <div id="createError" class="hidden items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-3 py-2.5 rounded-xl">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p id="createErrorMsg" class="text-xs text-red-600 dark:text-red-400 font-medium"></p>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('createModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitCreate()" id="createSubmitBtn" class="modal-btn-primary">
            <svg id="createSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Buat Pengguna
        </button>
    </x-slot>
</x-app-modal>