"""
Logika konversi skor risiko menjadi keputusan akhir (ALLOW / OTP / BLOCK).

Dipisah ke modul tersendiri agar threshold dapat diuji secara independen
dan mudah diaudit oleh tim keamanan tanpa harus membaca kode model.
"""

from enum import Enum

from app.core.config import get_settings


class RiskDecision(str, Enum):
    """
    Tiga kemungkinan keputusan yang dikembalikan ke Laravel.

    Menggunakan str Enum agar nilai dapat langsung diserialisasi ke JSON
    tanpa transformasi tambahan.
    """
    ALLOW = "ALLOW"
    OTP   = "OTP"
    BLOCK = "BLOCK"


def score_to_decision(risk_score: float) -> RiskDecision:
    """
    Konversi skor risiko numerik (0–100) menjadi keputusan yang dapat dieksekusi.

    Threshold dibaca dari konfigurasi sehingga dapat diubah tanpa deploy ulang
    (cukup restart service setelah mengubah environment variable).
    """
    settings = get_settings()

    score = float(risk_score)

    if score < settings.RISK_THRESHOLD_ALLOW:
        return RiskDecision.ALLOW
    elif score < settings.RISK_THRESHOLD_OTP:
        return RiskDecision.OTP
    else:
        return RiskDecision.BLOCK


def clamp_score(score: float, low: float = 0.0, high: float = 100.0) -> float:
    """
    Pastikan skor selalu berada dalam rentang 0–100.

    Dibutuhkan karena penggabungan skor AI + rule-based dapat menghasilkan
    nilai di luar rentang akibat pembulatan floating point.
    """
    return max(low, min(high, score))
