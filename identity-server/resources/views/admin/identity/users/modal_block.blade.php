<x-app-modal id="blockModal" maxWidth="sm" title="Blokir Pengguna" description="Tentukan alasan dan durasi pemblokiran akses untuk pengguna ini." icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'/></svg>" iconColor="red">
    <input type="hidden" id="blockUserId"/>
    <input type="hidden" id="blockUserName"/>
    <div class="space-y-4">
        <div id="blockModalSub" class="text-[10px] font-mono text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded inline-block mb-2 italic"></div>
        <div>
            <label>Alasan Blokir <span class="text-red-500">*</span></label>
            <select id="blockReason" class="appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
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
            <label>Blokir Sampai <span class="text-gray-400 capitalize">(opsional)</span></label>
            <input id="blockUntil" type="datetime-local" class="font-mono"/>
            <p class="text-[10px] text-gray-400 mt-1.5 ml-1">Kosongkan untuk blokir permanen</p>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('blockModal')" class="modal-btn-cancel flex-1">Batal</button>
        <button onclick="submitBlock()" class="modal-btn-danger flex-1">Konfirmasi Blokir</button>
    </x-slot>
</x-app-modal>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL: BULK BLOCK
═══════════════════════════════════════════════════════════════════════════ --}}
<x-app-modal id="bulkBlockModal" maxWidth="sm" title="Blokir Massal" description="Terpilih beberapa akun untuk dilakukan tindakan blokir sekaligus." icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'/></svg>" iconColor="red">
    <div class="space-y-4">
        <div id="bulkBlockCount" class="text-[10px] font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-1 rounded inline-block mb-2 uppercase tracking-wider"></div>
        <div>
            <label>Alasan Blokir <span class="text-red-500">*</span></label>
            <select id="bulkBlockReason" class="appearance-none bg-no-repeat" style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E\");background-position:right 10px center;background-size:14px;padding-right:32px">
                <option value="">-- Pilih alasan --</option>
                <option value="Suspicious activity">Aktivitas mencurigakan</option>
                <option value="Security threat">Ancaman keamanan</option>
                <option value="Manual review">Review manual admin</option>
            </select>
        </div>
    </div>

    <x-slot name="footer">
        <button onclick="AppModal.close('bulkBlockModal')" class="modal-btn-cancel flex-1">Batal</button>
        <button onclick="submitBulkBlock()" class="modal-btn-danger flex-1">Blokir Semua</button>
    </x-slot>
</x-app-modal>