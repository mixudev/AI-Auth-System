"""
Router untuk endpoint penilaian risiko.

Tanggung jawab layer ini dibatasi pada:
1. Validasi input (dihandle Pydantic secara otomatis)
2. Autentikasi (dihandle dependency verify_api_key)
3. Memanggil service layer
4. Mengembalikan respons

Tidak ada logika bisnis di sini — semua ada di services/.
"""

from __future__ import annotations

import logging

from fastapi import APIRouter, Depends, HTTPException, Request, status

from app.core.security import verify_api_key
from app.schemas.risk_input import RiskInputSchema, RiskOutputSchema
from app.services import predictor

logger = logging.getLogger(__name__)

router = APIRouter()


@router.post(
    "/risk-score",
    response_model=RiskOutputSchema,
    summary="Evaluasi Risiko Login",
    description=(
        "Menerima sinyal perilaku login dari Laravel dan mengembalikan "
        "skor risiko, keputusan, dan alasan yang dapat dijelaskan."
    ),
    status_code=status.HTTP_200_OK,
)
async def assess_risk(
    payload: RiskInputSchema,
    request: Request,
    _api_key: str = Depends(verify_api_key),
) -> RiskOutputSchema:
    """
    Endpoint utama: evaluasi risiko login berdasarkan sinyal perilaku.

    Input tidak mengandung PII — hanya sinyal perilaku yang telah diabstraksi.
    Respons selalu menyertakan reason_flags agar tidak ada keputusan yang bersifat
    "kotak hitam" dari perspektif tim keamanan.
    """
    # Log metadata request untuk audit trail
    # IP klien (Laravel) dicatat di sini, bukan IP pengguna akhir
    client_ip = request.client.host if request.client else "unknown"
    logger.info(
        "Risk assessment request | client_ip=%s | user_id=%d",
        client_ip,
        payload.user_id,
    )

    try:
        result = predictor.predict(payload)
    except Exception as exc:
        # Tangkap semua exception yang tidak tertangani di service layer
        # Kembalikan error generik ke klien, detail di log server
        logger.error(
            "Unhandled exception saat memproses user_id=%d: %s",
            payload.user_id,
            exc,
            exc_info=True,
        )
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Terjadi kesalahan internal. Coba lagi dalam beberapa saat.",
        ) from exc

    # Log keputusan BLOCK untuk monitoring insiden
    if result.decision == "BLOCK":
        logger.warning(
            "Login DIBLOKIR | user_id=%d | risk_score=%.1f | reasons=%s",
            payload.user_id,
            result.risk_score,
            result.reason_flags,
        )

    return result
