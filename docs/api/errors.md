# Error Codes

Halaman ini merangkum error code yang sering muncul di flow autentikasi.

## Error Code Utama

| Error Code | HTTP | Makna | Aksi Klien |
|---|---|---|---|
| `VALIDATION_FAILED` | 422 | Data request tidak valid | Tampilkan pesan validasi field |
| `CAPTCHA_REQUIRED` | 429 | Captcha wajib diisi/diverifikasi | Munculkan widget captcha dan submit ulang |
| `CAPTCHA_CONFIG_ERROR` | 503 | Mode captcha aktif tapi config tidak lengkap | Tampilkan pesan sistem dan informasikan admin |
| `TOO_MANY_ATTEMPTS` | 429 | Rate limit aktif | Ikuti `Retry-After` sebelum retry |
| `LOGIN_BLOCKED` | 403 | Login diblokir oleh kebijakan risiko | Tampilkan pesan blokir dan arahkan ke support |
| `USER_BLOCKED` | 403 | User sedang diblokir | Hubungi admin |
| `DEVICE_BLOCKED` | 403 | Device diblokir | Gunakan device terpercaya atau hubungi admin |
| `MFA_RATE_LIMITED` | 429 | Verifikasi MFA kena limit | Tunggu hingga cooldown selesai |

## Pola Response Error (JSON)

Contoh umum:

```json
{
  "message": "Terlalu banyak percobaan login gagal.",
  "error_code": "TOO_MANY_ATTEMPTS",
  "retry_after": 120
}
```

## Cara Menangani di Frontend

1. Selalu baca `error_code` jika tersedia.
2. Gunakan `retry_after` untuk countdown.
3. Jika `requires_captcha=true`, tampilkan captcha dan kirim `captcha_token`.
4. Bedakan error user-actionable (422/429) vs system-actionable (503).
