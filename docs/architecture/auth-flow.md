# Flow Autentikasi

Halaman ini merangkum alur autentikasi dari dua jalur: API dan Web.

## Flow API Login

```mermaid
flowchart TD
    A[POST /api/auth/login] --> B[PreAuthRateLimitMiddleware]
    B --> C{Allowed by limiter?}
    C -- No --> Z[429 Too Many Attempts]
    C -- Yes --> D[AuthFlowService attemptLogin]
    D --> E{Risk Decision}
    E -- ALLOW --> F[200 Authenticated]
    E -- OTP/MFA --> G[202/200 Requires MFA]
    E -- BLOCK --> H[403 Login Blocked]
```

## Flow Web Login

```mermaid
flowchart TD
    A[POST /login] --> B[PreAuthRateLimitMiddleware]
    B --> C[WebAuthController login]
    C --> D[AuthFlowService attemptLogin]
    D --> E{Result}
    E -- authenticated --> F[Redirect dashboard]
    E -- mfa_required --> G[Redirect halaman MFA]
    E -- error --> H[Back with validation/error]
```

## Flow MFA Verify

| Channel | Endpoint/Route | Keluaran |
|---|---|---|
| API | `POST /api/auth/mfa/verify` | JSON success/error |
| Web | `POST /auth/mfa/verify` | Redirect success/error |

## Flow Reset Password

1. Request reset link (`forgot-password`).
2. Validasi token reset.
3. Submit password baru.
4. Login ulang dengan kredensial baru.

## Titik Kontrol Keamanan

- Rate limit pre-auth.
- Verifikasi captcha (jika mode captcha aktif).
- Risk assessment AI/fallback.
- MFA throttle.
- Session fingerprint checks.
