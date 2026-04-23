# Ikhtisar API

Dokumentasi API dibagi menjadi dua kelompok:

1. API Authentication (publik + terlindungi token)
2. API AI Risk (internal service-to-service)

## Base URL

```text
http://<host>:8080/api
```

## Standar Header

| Header | Nilai | Catatan |
|---|---|---|
| `Accept` | `application/json` | Wajib untuk response JSON konsisten |
| `Content-Type` | `application/json` | Untuk request dengan body |
| `Authorization` | `Bearer <token>` | Wajib untuk endpoint terlindungi |

## Status Code Umum

| Code | Makna |
|---|---|
| `200` | Berhasil |
| `201` | Resource dibuat |
| `401` | Unauthorized |
| `403` | Forbidden/blocked |
| `422` | Validation error |
| `429` | Too many requests |
| `503` | Service unavailable |

## Daftar Dokumen API

- [Authentication API](/api/auth)
- [AI Risk API](/api/ai-risk)
- [Error Codes](/api/errors)
