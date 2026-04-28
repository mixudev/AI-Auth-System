# Changelog

Semua perubahan penting pada project MixuAuth akan dicatat di file ini.

## [2.0.0] - 2026-04-28

### Added
- Integrasi awal **Laravel 13** (versi terbaru).
- Support PHP 8.4 di lingkungan Docker.
- Sistem cache otomatis pada `setup.sh`.
- Dokumen profesional: `LICENSE`, `SECURITY.md`, `CONTRIBUTING.md`.

### Changed
- Upgrade Framework dari Laravel 11 ke Laravel 13.
- Update `laravel/tinker` ke v3.0 untuk kompatibilitas framework terbaru.
- Konfigurasi `session.cookie_samesite` diubah menjadi `Lax` untuk meningkatkan stabilitas alur SSO OAuth2.

### Fixed
- Perbaikan komentar versi pada `bootstrap/app.php`.
- Resolusi konflik dependency pada `nunomaduro/collision` dan `jenssegers/agent`.

---

## [1.0.0] - 2026-04-25
- Rilis awal MixuAuth Identity Server (Laravel 11).
- Fitur SSO OAuth2 Dasar.
- Integrasi FastAPI untuk AI Risk Assessment.
