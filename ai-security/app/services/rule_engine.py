"""
Rule-based risk scoring engine.

Tujuan utama modul ini ada dua:
1. Memberikan kontribusi skor yang dapat dijelaskan secara eksplisit (30% dari skor akhir)
2. Berfungsi sebagai fallback lengkap jika model AI gagal

Setiap aturan menghasilkan skor parsial DAN penjelasan (reason flag).
Ini memastikan tidak ada keputusan yang terjadi tanpa alasan yang dapat diaudit.

Bobot per aturan dikalibrasi agar total rule score berada di rentang 0–100.
"""

from __future__ import annotations

from dataclasses import dataclass, field

from app.schemas.risk_input import RiskInputSchema


# ------------------------------------------------------------------
# Konfigurasi bobot aturan
# Nilai ini dapat dipindahkan ke config.py jika perlu dikonfigurasi
# via environment variable di masa mendatang.
# ------------------------------------------------------------------

_RULE_WEIGHTS = {
    "vpn_usage":             20,   # VPN meningkatkan risiko anonimitas
    "high_ip_risk":          25,   # IP dengan reputasi buruk
    "new_device":            20,   # Perangkat tidak dikenal
    "new_country":           20,   # Login dari negara berbeda dari riwayat
    "failed_attempts":       25,   # Percobaan gagal berulang (dikalikan faktor)
    "high_request_speed":    20,   # Request terlalu cepat — indikasi bot/script
    "low_device_trust":      15,   # Perangkat dengan kepercayaan rendah
    "abnormal_login_hour":   10,   # Login di luar jam kerja
}

# Ambang batas untuk setiap aturan
_THRESHOLDS = {
    "high_ip_risk":          0.5,   # ip_risk_score di atas ini dianggap berisiko
    "high_request_speed":    0.7,   # request_speed di atas ini mencurigakan
    "low_device_trust":      0.3,   # device_trust_score di bawah ini berisiko
    "failed_attempts_cap":   5,     # Di atas ini dianggap brute-force aktif
}

# Jam kerja yang dianggap normal (06:00–21:59)
_NORMAL_HOUR_START: int = 6
_NORMAL_HOUR_END:   int = 22


@dataclass
class RuleBasedResult:
    """
    Hasil evaluasi rule engine.

    Menyimpan skor total dan daftar aturan yang terpicu
    untuk keperluan logging dan explainability.
    """
    score:        float       = 0.0
    reason_flags: list[str]   = field(default_factory=list)


def evaluate(data: RiskInputSchema) -> RuleBasedResult:
    """
    Evaluasi semua aturan risiko dan kembalikan skor agregat beserta alasannya.

    Skor mentah dapat melebihi 100 jika banyak aturan terpicu sekaligus.
    Nilai di-cap ke 100 di akhir untuk konsistensi.
    """
    raw_score:    float      = 0.0
    reason_flags: list[str]  = []

    # -- Aturan 1: Penggunaan VPN
    # VPN meningkatkan risiko karena menyembunyikan lokasi asli pengguna.
    if data.is_vpn == 1:
        raw_score += _RULE_WEIGHTS["vpn_usage"]
        reason_flags.append("vpn_usage")

    # -- Aturan 2: IP dengan reputasi risiko tinggi
    if data.ip_risk_score > _THRESHOLDS["high_ip_risk"]:
        # Skor proporsional: ip_risk_score 0.9 memberikan kontribusi lebih dari 0.6
        contribution = _RULE_WEIGHTS["high_ip_risk"] * (data.ip_risk_score / 1.0)
        raw_score += contribution
        reason_flags.append("high_risk_ip")

    # -- Aturan 3: Perangkat baru yang belum terdaftar
    if data.is_new_device == 1:
        raw_score += _RULE_WEIGHTS["new_device"]
        reason_flags.append("new_device_detected")

    # -- Aturan 4: Login dari negara yang tidak dikenal
    if data.is_new_country == 1:
        raw_score += _RULE_WEIGHTS["new_country"]
        reason_flags.append("new_country_detected")

    # -- Aturan 5: Percobaan login gagal berulang
    # Kontribusi skor meningkat secara linear hingga batas maksimum.
    if data.failed_attempts > 0:
        # Faktor normalisasi: 1 percobaan = 5%, 5 percobaan = 25% dari bobot penuh
        factor = min(data.failed_attempts / _THRESHOLDS["failed_attempts_cap"], 1.0)
        contribution = _RULE_WEIGHTS["failed_attempts"] * factor
        raw_score += contribution
        reason_flags.append(f"failed_attempts:{data.failed_attempts}")

    # -- Aturan 6: Kecepatan request mencurigakan (indikasi bot atau script otomatis)
    if data.request_speed > _THRESHOLDS["high_request_speed"]:
        raw_score += _RULE_WEIGHTS["high_request_speed"]
        reason_flags.append("high_request_speed")

    # -- Aturan 7: Kepercayaan perangkat rendah
    if data.device_trust_score < _THRESHOLDS["low_device_trust"]:
        # Kontribusi lebih besar jika device_trust_score mendekati 0
        factor = 1.0 - (data.device_trust_score / _THRESHOLDS["low_device_trust"])
        contribution = _RULE_WEIGHTS["low_device_trust"] * factor
        raw_score += contribution
        reason_flags.append("low_device_trust")

    # -- Aturan 8: Login di luar jam kerja normal
    hour = data.login_hour
    is_off_hours = hour < _NORMAL_HOUR_START or hour >= _NORMAL_HOUR_END
    if is_off_hours:
        raw_score += _RULE_WEIGHTS["abnormal_login_hour"]
        reason_flags.append("abnormal_login_hour")

    # Cap skor akhir ke 100
    final_score = min(raw_score, 100.0)

    return RuleBasedResult(score=final_score, reason_flags=reason_flags)
