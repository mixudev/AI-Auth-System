<x-app-modal id="editModal" maxWidth="lg" title="Edit Permission" description="Perbarui detail grup dan deskripsi izin." icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'/></svg>" iconColor="indigo">
    <div class="space-y-4">
        <div id="editModalSub" class="text-[10px] font-mono text-gray-400 dark:text-slate-500 bg-gray-50 dark:bg-white/5 px-2 py-1 rounded inline-block mb-2"></div>
        <input type="hidden" id="editPermId"/>

        <div>
            <label>Nama Permission</label>
            <input id="editName" type="text" disabled class="bg-slate-100 dark:bg-slate-800 opacity-60 font-mono cursor-not-allowed"/>
            <p class="text-[9px] text-slate-400 mt-1">Nama permission tidak dapat diubah setelah dibuat.</p>
        </div>
        
        <div>
            <label>Grup Fitur <span class="text-red-500">*</span></label>
            <input id="editGroup" type="text" placeholder="Masukkan grup baru..."/>
        </div>

        <div>
            <label>Deskripsi</label>
            <textarea id="editDescription" rows="3" placeholder="Apa yang diizinkan oleh permission ini?"></textarea>
        </div>
        
        <div id="editError" class="hidden flex items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-3 py-2.5 rounded-xl transition-all">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p id="editErrorMsg" class="text-xs text-red-600 dark:text-red-400 font-medium"></p>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('editModal')" class="modal-btn-cancel">Batal</button>
        <button onclick="submitEdit()" id="editSubmitBtn" class="modal-btn-primary">
            <svg id="editSpinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Simpan Perubahan
        </button>
    </x-slot>
</x-app-modal>
