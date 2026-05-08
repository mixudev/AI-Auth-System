"""
Modul autentikasi API Key untuk melindungi endpoint dari akses tidak sah.

Menggunakan Header X-API-Key agar mudah dikonfigurasi di reverse proxy
dan tidak terekspos di URL/log akses standar.

Keamanan berlapis:
1. API Key matching (constant-time comparison, anti timing-attack)
2. HMAC-SHA256 signature verification (anti tampering)
3. Timestamp window validation (anti replay attack, max 5 menit)
"""

from fastapi import HTTPException, Request, Security, status
from fastapi.security import APIKeyHeader

from app.core.config import get_settings

# FastAPI akan secara otomatis menambahkan X-API-Key ke dokumentasi OpenAPI
_api_key_header = APIKeyHeader(name="X-API-Key", auto_error=False)


async def verify_api_key(
    request: Request,
    api_key: str = Security(_api_key_header),
) -> str:
    """
    Dependency FastAPI untuk memverifikasi API Key pada setiap request.

    [FIX] Parameter 'request: Request' ditambahkan agar bisa mengakses
    request.headers (X-Timestamp, X-HMAC-Signature) dan request.body().
    Sebelumnya parameter ini tidak ada sehingga 'request' NameError fatal
    yang menyebabkan AI risk engine tidak pernah berjalan.
    """
    import hmac
    import hashlib
    from datetime import datetime, timezone

    settings = get_settings()

    # ── 1. Validasi keberadaan API Key ─────────────────────────────────────
    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="API Key tidak ditemukan. Sertakan header X-API-Key.",
            headers={"WWW-Authenticate": "ApiKey"},
        )

    # ── 2. API Key matching (Constant-time comparison, anti timing attack) ─
    if not hmac.compare_digest(api_key, settings.API_KEY):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="API Key tidak valid.",
        )

    # ── 3. Ambil header tambahan dari Laravel ──────────────────────────────
    timestamp        = request.headers.get("X-Timestamp")
    client_signature = request.headers.get("X-HMAC-Signature")

    if not timestamp or not client_signature:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="HMAC Signature and Timestamp headers are required.",
        )

    # ── 4. Timestamp window validation (Anti Replay Attack) ────────────────
    try:
        ts  = int(timestamp)
        now = int(datetime.now(timezone.utc).timestamp())
        if abs(now - ts) > 300:  # Tolak request lebih dari 5 menit
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Request expired (Replay Attack Protection).",
            )
    except ValueError:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Invalid Timestamp format.",
        )

    # ── 5. Validasi HMAC-SHA256 (Anti Tampering) ───────────────────────────
    # Laravel mengirim: HMAC-SHA256(raw_body + "|" + timestamp, API_KEY)
    body_bytes   = await request.body()
    data_to_sign = body_bytes.decode("utf-8") + "|" + timestamp

    expected_signature = hmac.new(
        key=settings.API_KEY.encode("utf-8"),
        msg=data_to_sign.encode("utf-8"),
        digestmod=hashlib.sha256,
    ).hexdigest()

    if not hmac.compare_digest(client_signature, expected_signature):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Invalid HMAC Signature. Integrity check failed.",
        )

    return api_key
