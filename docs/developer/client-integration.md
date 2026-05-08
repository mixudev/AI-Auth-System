# Integrasi Klien Lintas Platform

Sistem ini menggunakan standar OAuth2 dan OpenID Connect (OIDC), sehingga dapat diintegrasikan dengan hampir semua bahasa pemrograman atau framework modern.

## 1. React / Next.js (SPA & SSR)

Untuk aplikasi berbasis JavaScript, disarankan menggunakan alur **Authorization Code Flow dengan PKCE** untuk keamanan maksimal tanpa menyimpan client secret di sisi klien.

### Penggunaan Library OIDC
Gunakan library seperti `oidc-client-ts` atau `next-auth`.

**Contoh Konfigurasi (NextAuth.js):**
```javascript
import NextAuth from "next-auth"

export const authOptions = {
  providers: [
    {
      id: "mixuauth",
      name: "Mixu Identity",
      type: "oauth",
      wellKnown: "https://sso.domain.com/.well-known/openid-configuration",
      authorization: { params: { scope: "openid profile email" } },
      clientId: process.env.CLIENT_ID,
      clientSecret: process.env.CLIENT_SECRET, // Hanya untuk SSR
      profile(profile) {
        return {
          id: profile.sub,
          name: profile.name,
          email: profile.email,
        }
      },
    },
  ],
}
export default NextAuth(authOptions)
```

## 2. PHP Native (Tanpa Framework)

Jika Anda tidak menggunakan Laravel, Anda harus menangani alur OAuth2 secara manual menggunakan library seperti `guzzlehttp/guzzle`.

### Langkah-langkah Integrasi:

1. **Redirect ke Server**: Arahkan user ke endpoint authorize.
   ```php
   $query = http_build_query([
       'client_id' => 'your-id',
       'redirect_uri' => 'https://app.com/callback',
       'response_type' => 'code',
       'scope' => 'openid profile',
       'state' => $state,
   ]);
   header('Location: https://sso.domain.com/oauth/authorize?' . $query);
   ```

2. **Handle Callback**: Tangkap `code` dan tukar dengan token.
   ```php
   $response = $http->post('https://sso.domain.com/oauth/token', [
       'form_params' => [
           'grant_type' => 'authorization_code',
           'client_id' => 'your-id',
           'client_secret' => 'your-secret',
           'redirect_uri' => 'https://app.com/callback',
           'code' => $_GET['code'],
       ],
   ]);
   $tokens = json_decode($response->getBody(), true);
   ```

## 3. Referensi Endpoint Utama

Gunakan endpoint berikut untuk integrasi manual:

*   **Authorize**: `GET /oauth/authorize`
*   **Token**: `POST /oauth/token`
*   **User Info**: `GET /api/user`
*   **Revoke**: `POST /oauth/revoke`
*   **Discovery (OIDC)**: `GET /.well-known/openid-configuration`

## Keamanan Integrasi

*   **HTTPS**: Semua endpoint wajib diakses melalui HTTPS.
*   **State Parameter**: Selalu kirimkan dan validasi parameter `state` untuk mencegah serangan CSRF.
*   **Token Storage**: Simpan `access_token` dan `refresh_token` dengan aman (HttpOnly cookies untuk web, secure storage untuk mobile).
