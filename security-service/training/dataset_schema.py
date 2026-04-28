"""
Definisi skema dan asumsi dataset untuk training model.

File ini mendokumentasikan ASUMSI yang dibuat tentang data training.
Tim keamanan dan data harus membaca file ini sebelum mengumpulkan
atau menggunakan dataset untuk melatih ulang model.

PENTING: Isolation Forest dilatih hanya dengan data LOGIN NORMAL.
Tidak memerlukan data login "hacker" atau label anomali.
"""

from __future__ import annotations

from dataclasses import dataclass
from typing import ClassVar


@dataclass(frozen=True)
class DatasetColumnSpec:
    """Spesifikasi satu kolom dalam dataset training."""
    name:        str
    dtype:       str
    min_value:   float
    max_value:   float
    description: str


class DatasetSchema:
    """
    Dokumentasi skema CSV yang digunakan untuk training model.

    Dataset harus berisi HANYA login sukses yang dianggap sah.
    Sumber: login_logs dari database Laravel dengan status = 'success'.

    Asumsi:
    1. Dataset mewakili perilaku login pengguna yang valid selama periode normal
    2. Tidak mengandung percobaan brute-force atau login dari akun yang diretas
    3. Minimal 10.000 baris untuk menghasilkan model yang cukup representatif
    4. Distribusi data cukup beragam (berbagai jam, device, IP)

    Keterbatasan:
    - Model dapat menghasilkan false positive untuk pengguna dengan pola login tidak biasa
    - Jika data training terkontaminasi (mengandung serangan), model akan belajar pola salah
    - Model perlu dilatih ulang secara berkala (disarankan setiap 3 bulan)
    """

    COLUMNS: ClassVar[list[DatasetColumnSpec]] = [
        DatasetColumnSpec(
            name="ip_risk_score",
            dtype="float",
            min_value=0.0,
            max_value=1.0,
            description="Skor risiko IP yang dinormalisasi. 0=IP bersih, 1=IP sangat berisiko.",
        ),
        DatasetColumnSpec(
            name="is_vpn",
            dtype="int",
            min_value=0.0,
            max_value=1.0,
            description="1 jika login melalui VPN, 0 jika tidak.",
        ),
        DatasetColumnSpec(
            name="is_new_device",
            dtype="int",
            min_value=0.0,
            max_value=1.0,
            description="1 jika perangkat belum pernah digunakan sebelumnya.",
        ),
        DatasetColumnSpec(
            name="is_new_country",
            dtype="int",
            min_value=0.0,
            max_value=1.0,
            description="1 jika login dari negara yang berbeda dari riwayat.",
        ),
        DatasetColumnSpec(
            name="login_hour",
            dtype="int",
            min_value=0.0,
            max_value=23.0,
            description="Jam login dalam format 24 jam.",
        ),
        DatasetColumnSpec(
            name="failed_attempts",
            dtype="int",
            min_value=0.0,
            max_value=10.0,
            description="Jumlah percobaan gagal dalam 30 menit terakhir sebelum login sukses.",
        ),
        DatasetColumnSpec(
            name="request_speed",
            dtype="float",
            min_value=0.0,
            max_value=1.0,
            description="Kecepatan request dinormalisasi. 0=sangat lambat, 1=sangat cepat.",
        ),
        DatasetColumnSpec(
            name="device_trust_score",
            dtype="float",
            min_value=0.0,
            max_value=1.0,
            description="Tingkat kepercayaan perangkat. 0=tidak dipercaya, 1=sangat dipercaya.",
        ),
    ]

    REQUIRED_MIN_ROWS: ClassVar[int] = 10_000

    @classmethod
    def column_names(cls) -> list[str]:
        return [col.name for col in cls.COLUMNS]

    @classmethod
    def validate_dataframe(cls, df) -> list[str]:
        """
        Validasi DataFrame pandas sebelum digunakan untuk training.
        Mengembalikan list pesan error, kosong jika semua valid.
        """
        errors: list[str] = []
        import pandas as pd

        missing = set(cls.column_names()) - set(df.columns)
        if missing:
            errors.append(f"Kolom yang hilang: {missing}")

        if len(df) < cls.REQUIRED_MIN_ROWS:
            errors.append(
                f"Dataset terlalu kecil: {len(df)} baris, minimal {cls.REQUIRED_MIN_ROWS}"
            )

        for col_spec in cls.COLUMNS:
            if col_spec.name not in df.columns:
                continue
            if df[col_spec.name].isnull().any():
                errors.append(f"Kolom '{col_spec.name}' mengandung nilai null.")
            out_of_range = df[
                (df[col_spec.name] < col_spec.min_value)
                | (df[col_spec.name] > col_spec.max_value)
            ]
            if not out_of_range.empty:
                errors.append(
                    f"Kolom '{col_spec.name}' memiliki {len(out_of_range)} "
                    f"baris di luar rentang [{col_spec.min_value}, {col_spec.max_value}]."
                )

        return errors
