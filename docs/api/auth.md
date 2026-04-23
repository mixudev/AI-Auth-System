# Authentication API

Berikut endpoint API pada modul autentikasi (`app/Modules/Authentication/routes/api.php`).

## Public Endpoints

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/auth/login` | Login dengan evaluasi risiko |
| POST | `/api/auth/mfa/verify` | Verifikasi MFA setelah challenge |
| POST | `/api/auth/forgot-password` | Kirim link reset password |
| POST | `/api/auth/reset-password` | Submit password baru |
| GET | `/api/auth/reset-password/validate` | Validasi token reset |

## Protected Endpoints

| Method | Endpoint | Middleware |
|---|---|---|
| POST | `/api/auth/logout` | `auth:sanctum`, session guards |

## POST /api/auth/login

### Request

```json
{
  "email": "user@example.com",
  "password": "secret",
  "remember": false,
  "captcha_token": "optional-token-when-required"
}
```

### Response (authenticated)

```json
{
  "message": "Login berhasil.",
  "user": {
    "id": 1,
    "name": "User",
    "email": "user@example.com"
  }
}
```

### Response (requires MFA)

```json
{
  "message": "Verifikasi lanjutan diperlukan.",
  "requires_mfa": true,
  "session_token": "64-char-token",
  "expires_in": 300,
  "mfa_type": "email"
}
```

### Response (captcha required)

```json
{
  "message": "Verifikasi Keamanan diperlukan.",
  "error_code": "CAPTCHA_REQUIRED",
  "requires_captcha": true
}
```

## POST /api/auth/mfa/verify

### Request

```json
{
  "session_token": "64-char-token",
  "code": "123456"
}
```

### Response

```json
{
  "message": "Verifikasi berhasil.",
  "user": {
    "id": 1,
    "email": "user@example.com"
  }
}
```

## POST /api/auth/forgot-password

### Request

```json
{
  "email": "user@example.com"
}
```

### Response

```json
{
  "message": "Jika email terdaftar, link reset telah dikirim."
}
```

## POST /api/auth/reset-password

### Request

```json
{
  "email": "user@example.com",
  "token": "reset-token",
  "password": "StrongPassword#2026",
  "password_confirmation": "StrongPassword#2026"
}
```

### Response

```json
{
  "message": "Password berhasil diperbarui."
}
```

## POST /api/auth/logout

Header:

```http
Authorization: Bearer <token>
Accept: application/json
```

Response:

```json
{
  "message": "Anda berhasil keluar dari sistem."
}
```
