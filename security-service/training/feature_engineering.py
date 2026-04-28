"""
Transformasi fitur untuk dataset training.

Modul ini HARUS menghasilkan transformasi yang identik dengan
app/utils/normalizer.py yang digunakan saat inference.

Jika ada perubahan transformasi di sini, normalizer.py juga HARUS diperbarui,
dan model HARUS dilatih ulang. Ketidaksesuaian antara keduanya adalah
sumber bug yang paling umum dalam sistem ML production.
"""

from __future__ import annotations

import math

import numpy as np
import pandas as pd

from training.dataset_schema import DatasetSchema


def engineer_features(df: pd.DataFrame) -> np.ndarray:
    """
    Terapkan semua transformasi fitur pada DataFrame training.

    Mengembalikan numpy array yang siap digunakan oleh Isolation Forest.
    Urutan kolom output HARUS sama dengan yang ada di normalizer.py.
    """
    df = df.copy()

    # -- Transformasi jam login: linear ke siklik (sin/cos)
    # Mengatasi masalah "jam 23 jauh dari jam 0" dalam representasi linear.
    angles           = 2.0 * math.pi * df["login_hour"] / 24.0
    df["hour_sin"]   = np.sin(angles)
    df["hour_cos"]   = np.cos(angles)

    # -- Normalisasi failed_attempts ke 0–1
    FAILED_SCALE = 5.0
    df["failed_norm"] = (df["failed_attempts"] / FAILED_SCALE).clip(upper=1.0)

    # -- Kolom akhir dalam urutan yang konsisten dengan normalizer.py
    feature_columns = [
        "ip_risk_score",
        "is_vpn",
        "is_new_device",
        "is_new_country",
        "hour_sin",
        "hour_cos",
        "failed_norm",
        "request_speed",
        "device_trust_score",
    ]

    return df[feature_columns].values.astype(np.float64)


def get_output_feature_names() -> list[str]:
    """
    Kembalikan nama fitur output setelah transformasi.
    Harus identik dengan app/utils/normalizer.py::get_feature_names()
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
