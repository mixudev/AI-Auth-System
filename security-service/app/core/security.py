"""
Modul autentikasi API Key untuk melindungi endpoint dari akses tidak sah.

Menggunakan Header X-API-Key agar mudah dikonfigurasi di reverse proxy
dan tidak terekspos di URL/log akses standar.
"""

from fastapi import HTTPException, Security, status
from fastapi.security import APIKeyHeader

from app.core.config import get_settings

# FastAPI akan secara otomatis menambahkan X-API-Key ke dokumentasi OpenAPI
_api_key_header = APIKeyHeader(name="X-API-Key", auto_error=False)


async def verify_api_key(api_key: str = Security(_api_key_header)) -> str:
    """
    Dependency FastAPI untuk memverifikasi API Key pada setiap request.

    Menggunakan perbandingan string standar Python.
    Untuk production skala besar, pertimbangkan HMAC atau JWT.
    """
    settings = get_settings()

    import hmac
    import hashlib
    import json
    from datetime import datetime, timezone

    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="API Key tidak ditemukan. Sertakan header X-API-Key.",
            headers={"WWW-Authenticate": "ApiKey"},
        )

    # 1. API Key matching (Constant time comparison)
    if not hmac.compare_digest(api_key, settings.API_KEY):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="API Key tidak valid.",
        )

    # 2. Ambil header tambahan dari Laravel (H-06)
    timestamp = request.headers.get("X-Timestamp")
    client_signature = request.headers.get("X-HMAC-Signature")

    # Jika salah satu header tidak ada, tolak request (hanya terapkan jika system enforcement strict)
    if not timestamp or not client_signature:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="HMAC Signature and Timestamp headers are required.",
        )
    
    # 3. Mencegah Replay Attack (Misal: tolak request lebih dari 5 menit yang lalu)
    try:
        ts = int(timestamp)
        now = int(datetime.now(timezone.utc).timestamp())
        if abs(now - ts) > 300: # 5 menit
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Request expired (Replay Attack Protection).",
            )
    except ValueError:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Invalid Timestamp format.",
        )

    # 4. Validasi HMAC (sha256( raw_body + "|" + timestamp ))
    # Karena Laravel menggunakan json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    # Konsistensi spasi bisa jadi rentan jika dibandingkan dari byte streaming raw_body.
    # Namun menggunakan raw_body asli adalah standar terbaik.
    body_bytes = await request.body()
    data_to_sign = body_bytes.decode("utf-8") + "|" + timestamp

    expected_signature = hmac.new(
        key=settings.API_KEY.encode("utf-8"),
        msg=data_to_sign.encode("utf-8"),
        digestmod=hashlib.sha256
    ).hexdigest()

    if not hmac.compare_digest(client_signature, expected_signature):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Invalid HMAC Signature. Integrity check failed.",
        )

    return api_key
