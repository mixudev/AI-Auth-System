# Panduan Penggunaan dan Pengujian API - MixuAuth

Dokumen ini berisi daftar endpoint API yang tersedia pada Identity Server serta prosedur teknis untuk melakukan pengujian.

---

## 1. Daftar Endpoint API Utama

Seluruh API menggunakan prefix dasar: `http://localhost:8080/`

### 1.1 Autentikasi (Public & Session-bound)

| Method | Endpoint | Fungsi | Middleware Utama |
| :--- | :--- | :--- | :--- |
| **POST** | `/api/auth/login` | Login user & cek risiko AI | PreAuthRateLimit |
| **POST** | `/api/auth/mfa/verify` | Verifikasi kode OTP | Throttle:mfa |
| **POST** | `/api/auth/forgot-password` | Kirim email reset password | PreAuthRateLimit |
| **POST** | `/api/auth/reset-password` | Update password baru | PreAuthRateLimit |
| **GET** | `/api/auth/reset-password/validate` | Validasi token reset | Public |
| **POST** | `/api/auth/logout` | Keluar dari sistem (Revoke token) | Auth:sanctum |

### 1.2 Resource & SSO (Bearer Token)

| Method | Endpoint | Fungsi | Proteksi |
| :--- | :--- | :--- | :--- |
| **GET** | `/api/user` | Mengambil info profil user | Auth:api (Passport) |
| **POST** | `/api/logout` | Global logout SSO | Auth:api (Passport) |
| **POST** | `/api/whatsapp/send` | Mengirim pesan via Gateway | Permission Required |

---

## 2. Cara Menguji API

### 2.1 Menggunakan Postman (Rekomendasi)
1. **Set Header**: Tambahkan `Accept: application/json` dan `Content-Type: application/json`.
2. **Body**: Pilih tab `raw` dan format `JSON`.
3. **Autentikasi**: Untuk endpoint yang diproteksi, pilih tab `Authorization` -> `Bearer Token`, lalu masukkan token yang didapat dari proses login.

### 2.2 Menggunakan cURL (Terminal/CMD)
Contoh pengujian login:
```bash
curl -X POST http://localhost:8080/api/auth/login \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -d '{"email": "admin@example.com", "password": "password_anda"}'
```

### 2.3 Menggunakan Browser Console (F12)
Jika Anda sedang membuka halaman web aplikasi Anda, Anda bisa mengetes API langsung via tab **Console**:
```javascript
fetch('/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        email: 'admin@example.com',
        password: 'password_anda'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## 3. Parameter Input Penting

### Login (`POST /api/auth/login`)
```json
{
    "email": "string",
    "password": "string",
    "device_id": "optional_string"
}
```

### Verifikasi MFA (`POST /api/auth/mfa/verify`)
```json
{
    "code": "123456"
}
```

---

## 4. Troubleshooting Pengujian
*   **419 Page Expired**: Terjadi jika Anda memanggil endpoint web tanpa CSRF token. Pastikan gunakan prefix `/api/`.
*   **401 Unauthorized**: Token Bearer salah, kadaluarsa, atau Anda belum mengirimkan header Authorization.
*   **422 Unprocessable Entity**: Validasi gagal (misal: format email salah atau password kurang panjang).

---
Copyright (c) 2026 MixuDev Security Team
