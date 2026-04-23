# AI Risk API

API ini berjalan di service `fastapi-risk` dan digunakan internal oleh Laravel.

## Endpoint Utama

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/v1/risk-score` | Menghitung skor risiko login |
| GET | `/health` | Health check service |

## Authentication Header

API internal menggunakan API key antar service.

```http
X-API-Key: <AI_API_KEY>
Content-Type: application/json
Accept: application/json
```

## POST /api/v1/risk-score

### Contoh Request

```json
{
  "ip_risk_score": 45,
  "is_vpn": false,
  "is_new_device": true,
  "is_new_country": false,
  "login_hour": 14,
  "failed_attempts": 2,
  "request_speed": 1.1,
  "device_trust_score": 0.7
}
```

### Contoh Response

```json
{
  "risk_score": 57.3,
  "decision": "OTP",
  "reason_flags": ["new_device", "failed_attempts:2"],
  "confidence": 0.86
}
```

## GET /health

Contoh response:

```json
{
  "status": "ok"
}
```

## Catatan Integrasi

- Endpoint AI tidak untuk akses publik internet.
- URL default internal: `http://fastapi-risk:8000`.
- Jika timeout/error, Laravel menggunakan fallback scoring sesuai konfigurasi.
