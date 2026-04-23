<x-app-modal id="createModal" maxWidth="lg" title="Tambah Permission" description="Buat izin baru untuk fitur spesifik dalam sistem." icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/></svg>" iconColor="indigo">
    <div class="space-y-4">
        <div>
            <label>Nama Permission <span class="text-red-500">*</span></label>
            <input id="createName" type="text" placeholder="mis: orders.create" class="font-mono"/>
            <p class="text-[9px] text-slate-400 mt-1 italic">Gunakan format dot notation: module.action (contoh: users.edit)</p>
        </div>
        
        <div>
            <label>Grup Fitur <span class="text-red-500">*</span></label>
            <div class="relative">
                <input id="createGroup" type="text" list="groupList" placeholder="Ketik atau pilih grup..."/>
                <datalist id="groupList">
                    @foreach($groups as $grp)
                    <option value="{{ $grp }}">
                    @endforeach
                </datalist>
            </div>
        </div>

        <div>
            <label>Deskripsi</label>
            <textarea id="createDescription" rows="3" placeholder="Apa yang diizinkan oleh permission ini?"></textarea>
        </div>
        
        <div id="createError" class="hidden flex items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-3 py-2.5 rounded-xl transition-all">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p id="createErrorMsg" class="text-xs text-red-600 dark:text-red-400 font-medium"></p>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('createModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitCreate()" id="createSubmitBtn" class="modal-btn-primary">
            <svg id="createSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Simpan Permission
        </button>
    </x-slot>
</x-app-modal>
