"""
Script untuk menghasilkan data dummy (sintetis) untuk training awal model.
Gunakan ini jika Anda belum memiliki cukup data real di database Laravel.

Data yang dihasilkan mengikuti pola "login normal":
- IP risk rendah
- Bukan VPN
- Device lama
- Jam kerja normal
- Sedikit kegagalan login
"""

import pandas as pd
import numpy as np
import argparse
from pathlib import Path

def generate_data(output_path: str, n_samples: int = 1000):
    print(f"Generating {n_samples} synthetic normal login samples...")
    
    data = {
        'ip_risk_score':      np.random.beta(1, 10, n_samples) * 0.3, # Mayoritas rendah
        'is_vpn':             np.random.choice([0, 1], n_samples, p=[0.95, 0.05]),
        'is_new_device':      np.random.choice([0, 1], n_samples, p=[0.8, 0.2]),
        'is_new_country':     np.random.choice([0, 1], n_samples, p=[0.98, 0.02]),
        'login_hour':         np.random.choice(range(24), n_samples, p=np.array([
            0.01, 0.01, 0.01, 0.01, 0.02, 0.05, # 00-05
            0.1, 0.12, 0.15, 0.1, 0.08, 0.07,  # 06-11
            0.05, 0.05, 0.05, 0.04, 0.03, 0.03, # 12-17
            0.01, 0.01, 0.01, 0.01, 0.01, 0.01  # 18-23
        ]) / np.sum([
            0.01, 0.01, 0.01, 0.01, 0.02, 0.05,
            0.1, 0.12, 0.15, 0.1, 0.08, 0.07,
            0.05, 0.05, 0.05, 0.04, 0.03, 0.03,
            0.01, 0.01, 0.01, 0.01, 0.01, 0.01
        ])),
        'failed_attempts':    np.random.choice(range(5), n_samples, p=[0.85, 0.1, 0.03, 0.01, 0.01]),
        'request_speed':      np.random.beta(2, 5, n_samples) * 0.4,
        'device_trust_score': np.random.beta(8, 2, n_samples) # Mayoritas tinggi
    }
    
    df = pd.DataFrame(data)
    
    # Simpan ke CSV
    Path(output_path).parent.mkdir(parents=True, exist_ok=True)
    df.to_csv(output_path, index=False)
    print(f"Success! Synthetic dataset saved to: {output_path}")

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--output", type=str, default="data/synthetic_login_data.csv")
    parser.add_argument("--n", type=int, default=1000)
    args = parser.parse_args()
    
    generate_data(args.output, args.n)
