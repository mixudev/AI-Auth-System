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

    if not api_key:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="API Key tidak ditemukan. Sertakan header X-API-Key.",
            headers={"WWW-Authenticate": "ApiKey"},
        )

    # Perbandingan langsung — cukup aman untuk internal network
    # Jika perlu constant-time comparison: gunakan hmac.compare_digest()
    if api_key != settings.API_KEY:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="API Key tidak valid atau tidak memiliki akses.",
        )

    return api_key
