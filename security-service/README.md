# AI Risk Detection Service

FastAPI service untuk menilai risiko login secara real-time.
Dirancang sebagai komponen internal yang hanya dapat diakses oleh aplikasi Laravel,
bukan oleh publik.

---

## Tujuan Sistem

Layanan ini TIDAK mengautentikasi pengguna.
Layanan ini mengevaluasi **risiko perilaku login** dan mengembalikan satu dari tiga keputusan:

| Keputusan | Kondisi                | Aksi di Laravel               |
|-----------|------------------------|-------------------------------|
| `ALLOW`   | risk_score < 30        | Login langsung diterima       |
| `OTP`     | 30 ≤ risk_score < 60   | Kirim kode OTP, tahan sesi    |
| `BLOCK`   | risk_score ≥ 60        | Tolak login, catat insiden    |

---

## Alur Data

```
Laravel (AuthController)
  │
  ├─ POST /api/v1/risk-score
  │    Payload: sinyal perilaku login (tanpa PII)
  │
  ▼
FastAPI (app/api/risk.py)
  │
  ├─ Validasi input (Pydantic)
  ├─ Autentikasi API Key
  │
  ├─ Rule Engine (app/services/rule_engine.py)
  │    → rule_score: 0–100
  │    → reason_flags dari aturan eksplisit
  │
  ├─ AI Model (app/services/predictor.py)
  │    → Isolation Forest inference
  │    → ai_score: 0–100
  │    → ai_reason_flags dari perturbasi fitur
  │    [gagal] → fallback: ai_score = 0, is_fallback = True
  │
  ├─ Penggabungan Skor
  │    final_score = (ai_score × 0.7) + (rule_score × 0.3)
  │
  └─ Keputusan + Respons JSON
       → risk_score, decision, reason_flags, is_fallback
```

---

## Arsitektur Hybrid

Skor akhir adalah gabungan dua sumber:

```
FINAL_RISK = (AI_RISK × 0.7) + (RULE_RISK × 0.3)
```

**Mengapa hybrid?**
- AI menangkap pola kompleks yang tidak mudah dirumuskan sebagai aturan
- Rule engine memberikan dasar yang dapat dijelaskan dan diaudit
- Jika AI gagal, rule engine mengambil alih sepenuhnya (tanpa auto-ALLOW)

---

## Proses Training Model

### Kebutuhan Dataset

Dataset harus berisi **hanya login sukses yang sah**.
Sumber yang disarankan: tabel `login_logs` Laravel dengan `status = 'success'`.

Kolom yang diperlukan:

| Kolom               | Tipe    | Rentang   | Keterangan                              |
|---------------------|---------|-----------|-----------------------------------------|
| `ip_risk_score`     | float   | 0.0–1.0   | Skor risiko IP yang telah dinormalisasi |
| `is_vpn`            | int     | 0/1       | Deteksi VPN                             |
| `is_new_device`     | int     | 0/1       | Perangkat baru                          |
| `is_new_country`    | int     | 0/1       | Negara baru                             |
| `login_hour`        | int     | 0–23      | Jam login                               |
| `failed_attempts`   | int     | 0–10      | Percobaan gagal sebelumnya              |
| `request_speed`     | float   | 0.0–1.0   | Kecepatan request                       |
| `device_trust_score`| float   | 0.0–1.0   | Kepercayaan perangkat                   |

Minimal **10.000 baris** untuk representasi yang cukup.

### Menjalankan Training

```bash
# Instalasi dependencies
pip install -r requirements.txt

# Training dengan dataset
python -m training.train_model \
  --dataset path/to/login_normal.csv \
  --output app/models/isolation_forest.pkl \
  --contamination 0.05 \
  --n-estimators 200
```

Parameter `contamination` (default: 0.05) menentukan proporsi data yang dianggap
sebagai outlier bahkan dalam dataset "normal". Nilai yang lebih tinggi membuat model
lebih sensitif tetapi meningkatkan false positive.

Setelah training selesai, dua file akan dihasilkan:
- `app/models/isolation_forest.pkl` — model yang siap digunakan
- `app/models/isolation_forest.json` — metadata training untuk audit

### Kapan Perlu Melatih Ulang?

- Setiap 3 bulan sebagai pembaruan rutin
- Setelah perubahan signifikan dalam pola penggunaan (migrasi region, perubahan kebijakan)
- Jika tingkat false positive meningkat di atas 5%
- Setelah insiden keamanan besar yang mengubah baseline perilaku

---

## Konfigurasi Deployment

### Environment Variables

Salin `.env.example` ke `.env` dan isi semua nilai:

```bash
cp .env.example .env
```

| Variable                   | Default | Keterangan                                     |
|----------------------------|---------|------------------------------------------------|
| `API_KEY`                  | —       | **Wajib diisi.** Kunci autentikasi dari Laravel|
| `APP_ENV`                  | production | `production` atau `development`             |
| `RISK_THRESHOLD_ALLOW`     | 30      | Batas skor untuk keputusan ALLOW               |
| `RISK_THRESHOLD_OTP`       | 60      | Batas skor untuk keputusan OTP (di bawah BLOCK)|
| `AI_RISK_WEIGHT`           | 0.7     | Bobot kontribusi model AI                      |
| `RULE_RISK_WEIGHT`         | 0.3     | Bobot kontribusi rule engine                   |
| `INFERENCE_TIMEOUT_SECONDS`| 2.0     | Timeout maksimum untuk prediksi AI             |
| `LOG_LEVEL`                | INFO    | Level logging: DEBUG, INFO, WARNING, ERROR     |
| `DEBUG_RESPONSES`          | false   | **Harus false di production**                  |

### Menjalankan dengan Docker

```bash
# Build image
docker build -t ai-risk-service:latest .

# Jalankan container
docker run -d \
  --name ai-risk-service \
  --env-file .env \
  -p 8000:8000 \
  ai-risk-service:latest
```

### Menjalankan Secara Lokal (Development)

```bash
pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

---

## Menjalankan Test

```bash
# Semua test
pytest

# Test dengan output verbose
pytest -v

# Test dengan coverage report
pytest --cov=app --cov-report=term-missing

# Hanya unit test tertentu
pytest tests/test_rule_engine.py -v
```

---

## Asumsi Keamanan

1. **Layanan ini tidak boleh diekspos ke publik.** Hanya dapat diakses dari network internal Docker atau VPN perusahaan.
2. **API Key harus dirotasi secara berkala** (disarankan setiap 90 hari).
3. **Tidak ada PII dalam payload.** Hanya sinyal perilaku yang telah diabstraksi.
4. **Log tidak mencatat API Key atau detail kredensial** dalam kondisi apapun.
5. **Model file (.pkl) tidak boleh dapat diunduh** dari luar container.

---

## Keterbatasan (Jujur)

Sistem ini memiliki keterbatasan yang perlu dipahami sebelum deployment:

1. **Isolation Forest tidak memiliki "ground truth" untuk serangan.** Model hanya tahu apa yang "normal", bukan apa yang merupakan serangan. Ini berarti serangan yang terlihat "normal" tidak akan terdeteksi.

2. **Model dapat mengalami drift seiring waktu.** Perilaku pengguna berubah. Model yang dilatih 6 bulan lalu mungkin tidak lagi merepresentasikan pola saat ini.

3. **False positive mungkin terjadi** untuk pengguna dengan pola login yang tidak biasa (misal: sering berpindah negara karena perjalanan bisnis).

4. **Explainability berbasis perturbasi adalah aproksimasi**, bukan nilai pasti. Reason flags mencerminkan kontributor yang paling signifikan, bukan daftar lengkap.

5. **Sistem ini adalah lapisan tambahan, bukan pengganti** autentikasi yang baik. Password yang kuat, MFA, dan audit log tetap diperlukan.

---

## Struktur Direktori Lengkap

```
security-service/
├── app/
│   ├── main.py                   # Entry point, registrasi router & middleware
│   ├── health.py                 # Endpoint /health
│   ├── api/
│   │   └── risk.py               # Router endpoint /risk-score
│   ├── core/
│   │   ├── config.py             # Konfigurasi via environment variables
│   │   ├── security.py           # Autentikasi API Key
│   │   └── thresholds.py         # Konversi skor ke keputusan
│   ├── schemas/
│   │   └── risk_input.py         # Skema validasi input & output (Pydantic)
│   ├── services/
│   │   ├── predictor.py          # Orkestrasi prediksi AI + rule engine
│   │   ├── rule_engine.py        # Aturan risiko eksplisit
│   │   └── explainability.py     # Ekstraksi alasan dari model AI
│   ├── models/
│   │   └── isolation_forest.pkl  # Model terlatih (dihasilkan oleh training/)
│   └── utils/
│       └── normalizer.py         # Transformasi fitur untuk inference
├── training/
│   ├── train_model.py            # Script training
│   ├── dataset_schema.py         # Dokumentasi & validasi skema dataset
│   └── feature_engineering.py   # Transformasi fitur untuk training
├── tests/
│   ├── test_rule_engine.py
│   ├── test_thresholds.py
│   ├── test_normalizer.py
│   ├── test_predictor.py
│   └── test_api_risk.py
├── logs/                         # Log output (di-mount sebagai volume Docker)
├── conftest.py                   # Konfigurasi pytest global
├── pytest.ini
├── requirements.txt
├── Dockerfile
├── .env.example
└── README.md
```
