# Dokumentasi Training Model AI (Risk Assessment)

Dokumen ini menjelaskan alur kerja untuk melatih ulang model AI deteksi anomali login menggunakan algoritma **Isolation Forest**.

## 1. Konsep Dasar
Model AI ini menggunakan pendekatan *Unsupervised Learning*. Alih-alih belajar apa itu "serangan", model belajar pola "login normal" pengguna. Segala sesuatu yang menyimpang jauh dari pola normal akan ditandai sebagai anomali (Risiko Tinggi).

## 2. Persyaratan Data
Dataset training harus berupa file CSV yang berisi **HANYA** login sukses yang dianggap valid. Jika data training mengandung serangan, model akan "kebingungan" dan menganggap serangan sebagai hal normal.

### Skema Kolom (Features):
| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `ip_risk_score` | float | Skor risiko IP (0.0 - 1.0) |
| `is_vpn` | int | 1 jika VPN, 0 jika tidak |
| `is_new_device` | int | 1 jika perangkat baru bagi user |
| `is_new_country` | int | 1 jika negara baru bagi user |
| `login_hour` | int | Jam login (0 - 23) |
| `failed_attempts` | int | Jumlah gagal login sebelum sukses |
| `request_speed` | float | Kecepatan request (0.0 - 1.0) |
| `device_trust_score` | float | Tingkat kepercayaan perangkat (0.0 - 1.0) |

## 3. Langkah-Langkah Lab (Workflow)

### Langkah A: Mengumpulkan Data dari Database
Gunakan perintah Artisan di container Laravel untuk mengekstrak fitur login sukses:
```bash
php artisan ai:export-training-data --output=training_data_real.csv
```
Pindahkan file tersebut ke folder `security-service/data/`.

### Langkah B: (Opsional) Menggunakan Data Sintetis
Jika data di database belum cukup (minimal disarankan 10.000 baris), gunakan generator:
```bash
python training/generate_synthetic_data.py --output data/synthetic_data.csv --n 10000
```

### Langkah C: Menjalankan Training
Gunakan modul `training.train_model` untuk melatih model baru:
```bash
python -m training.train_model \
  --dataset data/synthetic_data.csv \
  --output app/models/isolation_forest.pkl \
  --contamination 0.05
```
*Parameter `--contamination` menentukan seberapa sensitif model terhadap anomali (default 5%).*

## 4. Deploy Model
Setelah training selesai, file berikut akan diperbarui:
- `app/models/isolation_forest.pkl` (File model biner)
- `app/models/isolation_forest.json` (Metadata & audit trail)

Layanan FastAPI akan otomatis menggunakan model baru saat startup. Anda dapat memverifikasi status model melalui endpoint health:
`GET http://localhost:8000/health`

## 5. Pemeliharaan
Disarankan untuk melatih ulang model setiap **3 bulan** atau jika terjadi perubahan besar pada pola perilaku pengguna di platform Anda.
