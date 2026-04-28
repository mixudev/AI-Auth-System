# Contributing to MixuAuth

Terima kasih telah tertarik untuk berkontribusi pada MixuAuth Identity Server!

## Standar Pengembangan

1. **Coding Style**: Kami mengikuti standar PSR-12. Silakan jalankan `php artisan pint` sebelum melakukan commit.
2. **Commit Message**: Gunakan format yang jelas, contoh: `fix: resolve session timeout issue` atau `feat: add support for webauthn`.
3. **Pull Requests**:
   - Pastikan branch Anda up-to-date dengan `main`.
   - Jelaskan perubahan yang Anda buat secara detail di deskripsi PR.
   - Sertakan unit test jika memungkinkan.

## Alur Pengembangan (Branching)

- `main`: Versi stabil/production.
- `develop`: Tempat integrasi fitur baru sebelum rilis.
- `feature/*`: Branch untuk pengembangan fitur spesifik.

## Lingkungan Pengembangan

Kami menggunakan Docker untuk konsistensi. Gunakan perintah `./setup.sh` untuk memulai lingkungan pengembangan Anda dengan cepat.
