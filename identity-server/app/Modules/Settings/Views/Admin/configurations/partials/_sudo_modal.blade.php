<x-app-modal 
    id="sudoModal" 
    title="Verifikasi Keamanan" 
    description="Masukkan password akun Admin Anda untuk mengonfirmasi perubahan konfigurasi sistem ini."
    icon='<i class="fa-solid fa-shield-halved text-xl"></i>'
    iconColor="red"
>
    <div class="mt-2">
        <label>Password Anda</label>
        <input type="password" x-model="sudoPassword" @keydown.enter="confirmSudo()" id="sudo-password-input" placeholder="••••••••">
    </div>

    <x-slot name="footer">
        <button @click="AppModal.close('sudoModal')" class="modal-btn-cancel">Batal</button>
        <button @click="confirmSudo()" class="modal-btn-primary">Konfirmasi & Simpan</button>
    </x-slot>
</x-app-modal>
