"""
Skema Pydantic untuk validasi input dan output API.

Validasi ketat di layer ini memastikan model AI tidak pernah menerima
data yang malformed, yang dapat menyebabkan prediksi tidak dapat dipercaya.

Semua field adalah nilai yang sudah diabstraksi — tidak ada PII.
"""

from __future__ import annotations

from typing import Annotated

from pydantic import BaseModel, Field, field_validator, model_validator


# ------------------------------------------------------------------
# Input dari Laravel
# ------------------------------------------------------------------

class RiskInputSchema(BaseModel):
    """
    Payload yang dikirim oleh Laravel ke endpoint POST /risk-score.

    Setiap field memiliki constraint ketat agar:
    1. Data anomali tidak merusak prediksi model
    2. Injeksi nilai ekstrem tidak mempengaruhi skor
    """

    user_id: Annotated[int, Field(gt=0, description="ID pengguna dari sistem Laravel")]

    ip_risk_score: Annotated[
        float,
        Field(ge=0.0, le=1.0, description="Skor risiko IP yang telah dinormalisasi (0=aman, 1=sangat berisiko)")
    ]

    is_vpn: Annotated[
        int,
        Field(ge=0, le=1, description="1 jika koneksi terdeteksi melalui VPN, 0 jika tidak")
    ]

    is_new_device: Annotated[
        int,
        Field(ge=0, le=1, description="1 jika perangkat belum pernah digunakan oleh pengguna ini")
    ]

    is_new_country: Annotated[
        int,
        Field(ge=0, le=1, description="1 jika negara asal login berbeda dari riwayat pengguna")
    ]

    login_hour: Annotated[
        int,
        Field(ge=0, le=23, description="Jam saat login dalam format 24 jam (waktu server)")
    ]

    failed_attempts: Annotated[
        int,
        Field(ge=0, le=100, description="Jumlah percobaan login gagal dalam 30 menit terakhir")
    ]

    request_speed: Annotated[
        float,
        Field(ge=0.0, le=1.0, description="Kecepatan request yang dinormalisasi (0=normal, 1=sangat cepat/mencurigakan)")
    ]

    device_trust_score: Annotated[
        float,
        Field(ge=0.0, le=1.0, description="Skor kepercayaan perangkat (0=tidak dipercaya, 1=sangat dipercaya)")
    ]

    @field_validator("failed_attempts")
    @classmethod
    def cap_failed_attempts(cls, v: int) -> int:
        """
        Batasi nilai failed_attempts untuk mencegah skor rule-based meledak
        akibat nilai ekstrem (misalnya jika counter cache tidak direset).
        """
        return min(v, 10)

    model_config = {
        "json_schema_extra": {
            "example": {
                "user_id": 42,
                "ip_risk_score": 0.15,
                "is_vpn": 0,
                "is_new_device": 1,
                "is_new_country": 0,
                "login_hour": 14,
                "failed_attempts": 0,
                "request_speed": 0.1,
                "device_trust_score": 0.3,
            }
        }
    }


# ------------------------------------------------------------------
# Output ke Laravel
# ------------------------------------------------------------------

class RiskOutputSchema(BaseModel):
    """
    Respons yang dikembalikan ke Laravel setelah penilaian risiko.

    Setiap keputusan disertai reasons agar:
    1. Tim keamanan dapat menginvestigasi insiden
    2. Laravel dapat menyimpan alasan di login_logs
    3. Tidak ada keputusan yang terjadi tanpa penjelasan
    """

    risk_score: Annotated[
        float,
        Field(ge=0.0, le=100.0, description="Skor risiko akhir setelah penggabungan AI + rule-based")
    ]

    decision: str = Field(description="Keputusan akhir: ALLOW | OTP | BLOCK")

    reason_flags: list[str] = Field(
        default_factory=list,
        description="Daftar alasan yang menjelaskan keputusan ini"
    )

    ai_score: float = Field(
        description="Kontribusi skor dari model AI (sebelum digabungkan)"
    )

    rule_score: float = Field(
        description="Kontribusi skor dari rule-based engine (sebelum digabungkan)"
    )

    is_fallback: bool = Field(
        default=False,
        description="True jika model AI gagal dan hanya rule-based yang digunakan"
    )


class HealthResponse(BaseModel):
    status: str
    model_loaded: bool
    version: str
