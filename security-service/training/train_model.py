"""
Script training Isolation Forest untuk deteksi anomali login.

Cara menjalankan:
  python -m training.train_model --dataset path/to/login_data.csv

Atau dengan argumen tambahan:
  python -m training.train_model \\
    --dataset data/login_normal.csv \\
    --output app/models/isolation_forest.pkl \\
    --contamination 0.05 \\
    --n-estimators 200

ASUMSI PENTING:
- Dataset HANYA berisi login sukses yang dianggap normal
- Tidak ada label "serangan" — model belajar pola normal dan menandai deviasi
- Contamination parameter menentukan proporsi anomali yang diharapkan ada
  dalam data training (bahkan data "normal" mungkin mengandung sedikit outlier)
"""

from __future__ import annotations

import argparse
import json
import pickle
import sys
from datetime import datetime, timezone
from pathlib import Path

import numpy as np
import pandas as pd
from sklearn.ensemble import IsolationForest
from sklearn.model_selection import train_test_split

# Tambahkan root project ke path agar import bekerja
sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from training.dataset_schema import DatasetSchema
from training.feature_engineering import engineer_features, get_output_feature_names


DEFAULT_OUTPUT_PATH = Path(__file__).resolve().parent.parent / "app" / "models" / "isolation_forest.pkl"
DEFAULT_CONTAMINATION = 0.05
DEFAULT_N_ESTIMATORS  = 100
DEFAULT_RANDOM_STATE  = 42


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Latih model Isolation Forest untuk deteksi anomali login.",
        formatter_class=argparse.ArgumentDefaultsHelpFormatter,
    )
    parser.add_argument(
        "--dataset",
        type=str,
        required=True,
        help="Path ke file CSV dataset login normal.",
    )
    parser.add_argument(
        "--output",
        type=str,
        default=str(DEFAULT_OUTPUT_PATH),
        help="Path output file model .pkl",
    )
    parser.add_argument(
        "--contamination",
        type=float,
        default=DEFAULT_CONTAMINATION,
        help=(
            "Proporsi anomali yang diharapkan dalam data training (0.0–0.5). "
            "Nilai lebih tinggi membuat model lebih agresif dalam menandai anomali."
        ),
    )
    parser.add_argument(
        "--n-estimators",
        type=int,
        default=DEFAULT_N_ESTIMATORS,
        help="Jumlah pohon dalam Isolation Forest. Lebih banyak = lebih akurat tapi lebih lambat.",
    )
    parser.add_argument(
        "--random-state",
        type=int,
        default=DEFAULT_RANDOM_STATE,
        help="Seed untuk reproduktibilitas hasil training.",
    )
    return parser.parse_args()


def load_and_validate_dataset(csv_path: str) -> pd.DataFrame:
    """
    Muat dataset dari CSV dan validasi sesuai DatasetSchema.

    Keluar dengan kode error jika dataset tidak memenuhi syarat.
    Validation error harus diperbaiki SEBELUM training, bukan setelahnya.
    """
    print(f"Memuat dataset dari: {csv_path}")

    try:
        df = pd.read_csv(csv_path)
    except FileNotFoundError:
        print(f"ERROR: File tidak ditemukan: {csv_path}")
        sys.exit(1)
    except Exception as exc:
        print(f"ERROR: Gagal membaca CSV: {exc}")
        sys.exit(1)

    print(f"Dataset dimuat: {len(df):,} baris, {len(df.columns)} kolom")

    errors = DatasetSchema.validate_dataframe(df)
    if errors:
        print("\nERROR: Dataset tidak valid:")
        for err in errors:
            print(f"  - {err}")
        sys.exit(1)

    print("Validasi dataset: OK")
    return df


def train(args: argparse.Namespace) -> None:
    """Alur training lengkap dari dataset ke model tersimpan."""

    # -- Langkah 1: Muat dan validasi dataset
    df = load_and_validate_dataset(args.dataset)

    # -- Langkah 2: Rekayasa fitur
    print("\nMenerapkan feature engineering...")
    X = engineer_features(df)
    print(f"Shape setelah feature engineering: {X.shape}")
    print(f"Fitur: {get_output_feature_names()}")

    # -- Langkah 3: Bagi data untuk evaluasi (tidak untuk hyperparameter tuning)
    # Split hanya digunakan untuk melaporkan konsistensi skor, bukan validasi
    X_train, X_eval = train_test_split(
        X, test_size=0.2, random_state=args.random_state
    )
    print(f"\nData training: {len(X_train):,} baris")
    print(f"Data evaluasi: {len(X_eval):,} baris")

    # -- Langkah 4: Training model
    print(f"\nMelatih Isolation Forest...")
    print(f"  n_estimators  = {args.n_estimators}")
    print(f"  contamination = {args.contamination}")
    print(f"  random_state  = {args.random_state}")

    model = IsolationForest(
        n_estimators=args.n_estimators,
        contamination=args.contamination,
        random_state=args.random_state,
        # max_samples='auto' menggunakan min(256, n_samples) — cukup untuk data kecil-menengah
        max_samples="auto",
        # n_jobs=-1 menggunakan semua CPU yang tersedia
        n_jobs=-1,
    )
    model.fit(X_train)
    print("Training selesai.")

    # -- Langkah 5: Evaluasi sederhana
    train_scores = model.score_samples(X_train)
    eval_scores  = model.score_samples(X_eval)

    # Anomali = nilai score lebih rendah (lebih negatif)
    # Untuk data NORMAL, sebagian besar skor harus mendekati 0
    train_anomaly_pct = (model.predict(X_train) == -1).mean() * 100
    eval_anomaly_pct  = (model.predict(X_eval)  == -1).mean() * 100

    print(f"\nHasil evaluasi:")
    print(f"  Mean score (train): {train_scores.mean():.4f}")
    print(f"  Mean score (eval) : {eval_scores.mean():.4f}")
    print(f"  Anomali terdeteksi (train): {train_anomaly_pct:.1f}%")
    print(f"  Anomali terdeteksi (eval) : {eval_anomaly_pct:.1f}%")

    if eval_anomaly_pct > args.contamination * 100 * 2:
        print(
            f"\nPERINGATAN: Persentase anomali pada eval set ({eval_anomaly_pct:.1f}%) "
            f"jauh lebih tinggi dari contamination parameter ({args.contamination * 100:.1f}%). "
            "Pertimbangkan untuk memeriksa kualitas data training."
        )

    # -- Langkah 6: Simpan model dengan metadata versioning
    output_path = Path(args.output)
    output_path.parent.mkdir(parents=True, exist_ok=True)

    # Tambahkan metadata ke dalam objek model sebagai attribute
    # agar dapat diaudit tanpa membaca file terpisah
    model._training_metadata = {
        "trained_at":          datetime.now(timezone.utc).isoformat(),
        "n_training_samples":  len(X_train),
        "n_eval_samples":      len(X_eval),
        "contamination":       args.contamination,
        "n_estimators":        args.n_estimators,
        "random_state":        args.random_state,
        "feature_names":       get_output_feature_names(),
        "eval_anomaly_pct":    round(float(eval_anomaly_pct), 2),
        "dataset_path":        args.dataset,
    }

    with open(output_path, "wb") as f:
        pickle.dump(model, f, protocol=pickle.HIGHEST_PROTOCOL)

    print(f"\nModel disimpan ke: {output_path}")
    print(f"Ukuran file: {output_path.stat().st_size / 1024:.1f} KB")

    # Simpan metadata training ke file JSON terpisah untuk kemudahan audit
    metadata_path = output_path.with_suffix(".json")
    with open(metadata_path, "w") as f:
        json.dump(model._training_metadata, f, indent=2)

    print(f"Metadata training disimpan ke: {metadata_path}")
    print("\nTraining berhasil diselesaikan.")


if __name__ == "__main__":
    args = parse_args()
    train(args)
