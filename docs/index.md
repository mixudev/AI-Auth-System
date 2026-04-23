---
layout: home

hero:
  name: "AI Auth System"
  text: "Dokumentasi Teknis dan Operasional"
  tagline: "Panduan lengkap untuk instalasi, arsitektur, API, dan troubleshooting sistem autentikasi berbasis AI risk scoring."
  actions:
    - theme: brand
      text: Mulai dari Panduan
      link: /guide/
    - theme: alt
      text: Lihat Arsitektur
      link: /architecture/modules
    - theme: alt
      text: Buka Referensi API
      link: /api/

features:
  - title: Dokumentasi Berbasis Flow
    details: Setiap bagian disusun berdasarkan alur nyata dari request login sampai keputusan keamanan.
  - title: Operasional Siap Pakai
    details: Tersedia runbook harian, prosedur perubahan env, dan langkah recovery yang bisa langsung dijalankan.
  - title: Sinkron dengan Implementasi
    details: Endpoint, error code, dan komponen disesuaikan dengan route dan konfigurasi di kode saat ini.
---

## Peta Dokumen

| Area | Isi | Link |
|---|---|---|
| Panduan | Instalasi, Docker, environment, runbook, troubleshooting | [/guide/](/guide/) |
| Arsitektur | Struktur modul, AI engine, flow autentikasi | [/architecture/modules](/architecture/modules) |
| API | Kontrak endpoint auth, AI risk, dan katalog error | [/api/](/api/) |

## Alur Baca yang Direkomendasikan

1. Mulai dari [Panduan Instalasi](/guide/installation).
2. Lanjut ke [Konfigurasi Environment](/guide/environment).
3. Pahami [Flow Autentikasi](/architecture/auth-flow).
4. Gunakan [Referensi API](/api/auth) saat integrasi frontend/mobile.
5. Simpan [Troubleshooting](/guide/troubleshooting) sebagai runbook insiden.

## Cakupan Sistem

Dokumentasi ini mencakup:

- Aplikasi Laravel (`app`, `worker`, `scheduler`)
- Layanan AI FastAPI (`fastapi-risk`)
- Infrastruktur Docker Compose (`nginx`, `db`, `redis`, `docs`)
- Kontrak API internal dan eksternal
- Praktik operasional dan debugging runtime
