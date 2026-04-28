"""
Modul explainability untuk menghasilkan reason flags dari prediksi model AI.

Isolation Forest tidak memiliki feature importance built-in seperti
tree classifier biasa. Modul ini menggunakan pendekatan perturbasi fitur:
setiap fitur diuji secara individual untuk mengukur kontribusinya
terhadap anomaly score akhir.

Pendekatan ini dipilih karena:
1. Tidak memerlukan library SHAP atau LIME yang berat
2. Hasilnya deterministik dan mudah diaudit
3. Cocok untuk realtime inference dengan latensi rendah
"""

from __future__ import annotations

import numpy as np
from sklearn.ensemble import IsolationForest

from app.utils.normalizer import build_feature_vector, get_feature_names
from app.schemas.risk_input import RiskInputSchema


# Ambang batas kontribusi fitur yang dianggap "signifikan"
# Fitur yang mengubah skor lebih dari ini dianggap penyebab anomali
_CONTRIBUTION_THRESHOLD: float = 0.05

# Pemetaan nama fitur teknis ke reason flag yang dapat dibaca manusia
_FEATURE_TO_REASON: dict[str, str] = {
    "ip_risk_score":         "high_risk_ip",
    "is_vpn":                "vpn_usage",
    "is_new_device":         "new_device_detected",
    "is_new_country":        "new_country_detected",
    "login_hour_sin":        "abnormal_login_time",
    "login_hour_cos":        "abnormal_login_time",   # Sama dengan sin (satu alasan)
    "failed_attempts_norm":  "multiple_failed_attempts",
    "request_speed":         "high_request_speed",
    "device_trust_score":    "low_device_trust",
}


def extract_ai_reasons(
    model: IsolationForest,
    data: RiskInputSchema,
    base_score: float,
) -> list[str]:
    """
    Identifikasi fitur mana yang paling berkontribusi terhadap skor anomali tinggi.

    Metode: untuk setiap fitur, ganti nilainya dengan nilai "normal" (median training),
    lalu ukur perbedaan skor. Perbedaan besar berarti fitur tersebut signifikan.

    Mengembalikan list reason flags yang unik (tidak ada duplikat).
    """
    feature_vector = build_feature_vector(data)
    feature_names  = get_feature_names()

    # Nilai "normal" untuk setiap fitur digunakan sebagai baseline perbandingan
    # Nilai ini merepresentasikan perilaku login yang paling umum
    _NORMAL_BASELINE = {
        "ip_risk_score":        0.1,
        "is_vpn":               0.0,
        "is_new_device":        0.0,
        "is_new_country":       0.0,
        "login_hour_sin":       0.0,   # Jam siang (12:00) → sin ≈ 0
        "login_hour_cos":       -1.0,  # Jam siang (12:00) → cos ≈ -1
        "failed_attempts_norm": 0.0,
        "request_speed":        0.1,
        "device_trust_score":   0.8,
    }

    significant_reasons: set[str] = set()

    for idx, feature_name in enumerate(feature_names):
        # Buat salinan vektor dengan fitur ini diganti nilai normal
        perturbed = feature_vector.copy()
        perturbed[0, idx] = _NORMAL_BASELINE.get(feature_name, 0.0)

        try:
            # Hitung skor dengan fitur yang diperturbasi
            perturbed_score = _isolation_score_to_risk(
                model.score_samples(perturbed)[0]
            )

            # Jika mengganti fitur ini menurunkan skor secara signifikan,
            # berarti fitur ini adalah kontributor utama anomali
            delta = base_score - perturbed_score
            if delta > _CONTRIBUTION_THRESHOLD * 100:
                reason = _FEATURE_TO_REASON.get(feature_name)
                if reason:
                    significant_reasons.add(reason)

        except Exception:
            # Perturbasi individual tidak boleh membatalkan seluruh respons
            continue

    return sorted(significant_reasons)


def _isolation_score_to_risk(raw_score: float) -> float:
    """
    Konversi raw anomaly score dari Isolation Forest ke rentang 0–100.

    Isolation Forest mengembalikan nilai negatif mendekati -1 untuk anomali
    dan nilai mendekati 0 untuk sampel normal.
    Transformasi: risk = clamp((−score) × 100, 0, 100)
    """
    risk = (-raw_score) * 100.0
    return max(0.0, min(100.0, risk))
