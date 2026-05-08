# Developer Guide

Selamat datang di Panduan Pengembang AI Auth System. Bagian ini ditujukan bagi pengembang yang ingin memperluas sistem atau mengintegrasikan aplikasi klien dengan Identity Server.

## Daftar Modul Kustom

Sistem ini memiliki beberapa modul kustom yang dirancang untuk mempermudah pengembangan dan meningkatkan keamanan:

### 1. [Timezone Management](./timezone.md)
Modul untuk menangani perbedaan zona waktu antara database (UTC) dan user secara otomatis menggunakan Blade directives dan Carbon macros.

### 2. [Audit Logging](./audit-log.md)
Sistem pencatatan aktivitas terpusat untuk memantau tindakan pengguna dan perubahan data penting di seluruh sistem.

### 3. [Security Client](./security-client.md)
Layanan untuk berinteraksi dengan AI Risk Engine, tersedia baik sebagai layanan internal server maupun melalui paket klien Laravel resmi.

---

## Integrasi Aplikasi Klien

Kami menyediakan berbagai opsi untuk menghubungkan aplikasi Anda ke dalam ekosistem SSO kami:

### [Panduan Integrasi Klien](./client-integration.md)
Panduan teknis mendalam untuk mengintegrasikan berbagai jenis platform:
- **Aplikasi JavaScript** (React, Next.js, Vue).
- **Aplikasi PHP Native** (Tanpa framework).
- **Mobile & Desktop** (Melalui standar OAuth2/OIDC).

---

## Standar Pengembangan

Saat mengembangkan modul baru atau memodifikasi yang sudah ada, harap perhatikan hal-hal berikut:

1. **Modularitas**: Gunakan struktur folder `app/Modules` untuk menjaga keteraturan kode.
2. **Security First**: Selalu gunakan middleware keamanan dan validasi risiko AI yang tersedia.
3. **Dokumentasi**: Pastikan setiap fitur baru atau perubahan API didokumentasikan di folder `docs/`.
