"""
Fungsi normalisasi fitur untuk mempersiapkan input sebelum dikirim ke model.

Isolation Forest bekerja pada ruang fitur numerik.
Normalisasi yang konsisten antara training dan inference adalah kritis:
jika transformasi berbeda, model akan memprediksi secara salah.

Modul ini adalah satu-satunya tempat transformasi fitur terjadi,
baik saat training maupun saat inference.
"""

from __future__ import annotations

import math
import numpy as np

from app.schemas.risk_input import RiskInputSchema


# Nilai maksimum failed_attempts yang dianggap "normal" sebelum dinormalisasi.
# Melebihi nilai ini akan dinormalisasi sebagai 1.0 (risiko maksimum).
FAILED_ATTEMPTS_SCALE: int = 5

# Batas kepercayaan perangkat yang dianggap "rendah"
DEVICE_TRUST_LOW_THRESHOLD: float = 0.3


def normalize_login_hour(hour: int) -> tuple[float, float]:
    """
    Encode jam login menggunakan transformasi siklik (sin/cos).

    Alasan: representasi linear (0–23) menyebabkan model menganggap
    jam 23 dan jam 0 sangat berbeda, padahal keduanya berdekatan secara waktu.
    Transformasi sin/cos mempertahankan kedekatan siklik ini.

    Mengembalikan dua nilai (sin dan cos) yang bersama-sama
    merepresentasikan posisi pada lingkaran 24 jam.
    """
    angle = 2.0 * math.pi * hour / 24.0
    return math.sin(angle), math.cos(angle)


def normalize_failed_attempts(attempts: int) -> float:
    """
    Normalisasi failed_attempts ke rentang 0–1.

    Sengaja menggunakan skala lebih rendah dari nilai maksimum teoritis
    agar perbedaan antara 0, 1, 2, 3 percobaan tetap terlihat.
    Nilai di atas FAILED_ATTEMPTS_SCALE di-cap ke 1.0.
    """
    return min(attempts / FAILED_ATTEMPTS_SCALE, 1.0)


def build_feature_vector(data: RiskInputSchema) -> np.ndarray:
    """
    Ubah RiskInputSchema menjadi vektor fitur numpy yang siap digunakan model.

    URUTAN FITUR HARUS KONSISTEN dengan urutan saat training.
    Jika urutan berubah, model harus dilatih ulang.

    Feature vector layout:
    [0] ip_risk_score          — sudah 0–1
    [1] is_vpn                 — 0 atau 1
    [2] is_new_device          — 0 atau 1
    [3] is_new_country         — 0 atau 1
    [4] login_hour_sin         — komponen siklik jam
    [5] login_hour_cos         — komponen siklik jam
    [6] failed_attempts_norm   — dinormalisasi ke 0–1
    [7] request_speed          — sudah 0–1
    [8] device_trust_score     — sudah 0–1 (0=tidak dipercaya)
    """
    hour_sin, hour_cos = normalize_login_hour(data.login_hour)
    failed_norm        = normalize_failed_attempts(data.failed_attempts)

    features = np.array([
        float(data.ip_risk_score),
        float(data.is_vpn),
        float(data.is_new_device),
        float(data.is_new_country),
        hour_sin,
        hour_cos,
        failed_norm,
        float(data.request_speed),
        float(data.device_trust_score),
    ], dtype=np.float64)

    return features.reshape(1, -1)  # Isolation Forest expects 2D array


def get_feature_names() -> list[str]:
    """
    Kembalikan nama fitur dalam urutan yang sama dengan build_feature_vector().

    Digunakan untuk debugging, logging, dan dokumentasi training.
    """
    return [
        "ip_risk_score",
        "is_vpn",
        "is_new_device",
        "is_new_country",
        "login_hour_sin",
        "login_hour_cos",
        "failed_attempts_norm",
        "request_speed",
        "device_trust_score",
    ]
