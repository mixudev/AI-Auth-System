"""
Entry point aplikasi FastAPI.

Bertanggung jawab atas:
1. Inisialisasi aplikasi dan konfigurasi global
2. Registrasi router
3. Konfigurasi logging terstruktur (JSON)
4. Loading model saat startup
5. Rate limiting global
6. Handler error global

Tidak ada logika bisnis di file ini.
"""

from __future__ import annotations

import logging
import logging.config
import sys
import time
from contextlib import asynccontextmanager
from typing import AsyncGenerator

from fastapi import FastAPI, Request, Response, status
from fastapi.exceptions import RequestValidationError
from fastapi.middleware.trustedhost import TrustedHostMiddleware
from fastapi.responses import JSONResponse
from starlette.middleware.base import BaseHTTPMiddleware

from app.api import risk as risk_router
from app.core.config import get_settings
from app.health import router as health_router
from app.services import predictor

# ------------------------------------------------------------------
# Konfigurasi logging terstruktur (JSON)
# Format JSON memudahkan parsing oleh SIEM (Elastic, Splunk, dll.)
# ------------------------------------------------------------------

def _configure_logging(log_level: str) -> None:
    """
    Konfigurasi logging ke stdout dalam format JSON.

    Menggunakan format sederhana karena library python-json-logger
    tidak termasuk dalam dependencies inti untuk mengurangi ketergantungan.
    Untuk deployment production, pertimbangkan integrasi dengan structlog.
    """
    numeric_level = getattr(logging, log_level.upper(), logging.INFO)

    logging.basicConfig(
        level=numeric_level,
        format='{"time": "%(asctime)s", "level": "%(levelname)s", "name": "%(name)s", "message": "%(message)s"}',
        datefmt="%Y-%m-%dT%H:%M:%S",
        stream=sys.stdout,
        force=True,
    )


# ------------------------------------------------------------------
# Lifespan: startup dan shutdown
# ------------------------------------------------------------------

@asynccontextmanager
async def lifespan(app: FastAPI) -> AsyncGenerator[None, None]:
    """
    Jalankan inisialisasi saat startup dan pembersihan saat shutdown.
    """
    settings = get_settings()
    _configure_logging(settings.LOG_LEVEL)

    logger = logging.getLogger(__name__)
    logger.info("Memulai %s v%s ...", settings.APP_NAME, settings.APP_VERSION)

    # Muat model AI — kegagalan tidak menghentikan startup
    model_ok = predictor.load_model()
    if not model_ok:
        logger.warning(
            "Model AI tidak dimuat. Layanan berjalan dalam mode rule-based fallback."
        )

    logger.info("Aplikasi siap menerima request.")
    yield

    # Pembersihan saat shutdown (belum ada yang perlu dibersihkan saat ini)
    logger.info("Aplikasi dihentikan.")


# ------------------------------------------------------------------
# Inisialisasi FastAPI
# ------------------------------------------------------------------

settings = get_settings()

app = FastAPI(
    title=settings.APP_NAME,
    version=settings.APP_VERSION,
    description="AI-based login risk assessment engine untuk sistem autentikasi Laravel.",
    # Matikan dokumentasi Swagger di production untuk mengurangi attack surface
    docs_url="/docs" if settings.APP_ENV != "production" else None,
    redoc_url="/redoc" if settings.APP_ENV != "production" else None,
    openapi_url="/openapi.json" if settings.APP_ENV != "production" else None,
    lifespan=lifespan,
)


# ------------------------------------------------------------------
# Middleware
# ------------------------------------------------------------------

class RequestTimingMiddleware(BaseHTTPMiddleware):
    """
    Catat waktu pemrosesan setiap request ke header X-Process-Time.
    Berguna untuk monitoring performa dan deteksi bottleneck.
    """
    async def dispatch(self, request: Request, call_next) -> Response:
        start = time.monotonic()
        response = await call_next(request)
        duration_ms = round((time.monotonic() - start) * 1000, 2)
        response.headers["X-Process-Time"] = f"{duration_ms}ms"
        return response


app.add_middleware(RequestTimingMiddleware)


# ------------------------------------------------------------------
# Handler error global
# ------------------------------------------------------------------

@app.exception_handler(RequestValidationError)
async def validation_error_handler(
    request: Request, exc: RequestValidationError
) -> JSONResponse:
    """
    Kembalikan error validasi Pydantic dalam format yang konsisten.
    Detail teknis disederhanakan untuk klien eksternal.
    """
    logger = logging.getLogger(__name__)
    logger.warning("Validasi input gagal: %s", exc.errors())

    return JSONResponse(
        status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
        content={
            "detail":     "Data input tidak valid.",
            "error_code": "VALIDATION_ERROR",
            "errors":     exc.errors(),
        },
    )


@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception) -> JSONResponse:
    """
    Tangkap semua exception yang tidak tertangani.
    Jangan bocorkan stack trace ke klien.
    """
    logger = logging.getLogger(__name__)
    logger.error("Unhandled exception: %s", exc, exc_info=True)

    return JSONResponse(
        status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        content={"detail": "Terjadi kesalahan internal."},
    )


# ------------------------------------------------------------------
# Registrasi router
# ------------------------------------------------------------------

app.include_router(health_router, tags=["Health"])
app.include_router(
    risk_router.router,
    prefix="/api/v1",
    tags=["Risk Assessment"],
)
