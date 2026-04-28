"""
Endpoint health check untuk monitoring dan orchestrator (Docker, Kubernetes).

Sengaja TIDAK mengekspos detail model (path, versi, hyperparameter)
agar tidak memberikan informasi yang berguna bagi penyerang.
"""

from __future__ import annotations

from fastapi import APIRouter
from fastapi.responses import JSONResponse

from app.core.config import get_settings
from app.schemas.risk_input import HealthResponse
from app.services.predictor import is_model_loaded

router = APIRouter()


@router.get(
    "/health",
    response_model=HealthResponse,
    summary="Health Check",
    description="Cek status layanan. Tidak memerlukan autentikasi.",
    include_in_schema=True,
)
async def health_check() -> JSONResponse:
    """
    Kembalikan status operasional layanan.

    model_loaded = False berarti hanya rule-based yang aktif,
    bukan berarti layanan mati. Laravel harus tetap dapat
    mengirim request dan menerima penilaian berbasis aturan.
    """
    settings     = get_settings()
    model_loaded = is_model_loaded()

    # HTTP 200 selama aplikasi berjalan normal
    # HTTP 503 hanya jika terjadi kegagalan kritis (belum diimplementasi di sini)
    return JSONResponse(
        status_code=200,
        content={
            "status":       "ok",
            "model_loaded": model_loaded,
            "version":      settings.APP_VERSION,
        },
    )
