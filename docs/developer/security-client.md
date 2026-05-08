# Security Client (AI Risk Engine)

Sistem ini dilengkapi dengan integrasi AI Risk Engine yang berjalan sebagai layanan terpisah (FastAPI). Untuk berinteraksi dengan layanan ini, terdapat dua metode utama: menggunakan layanan internal (di sisi server) atau menggunakan paket klien (di sisi aplikasi klien Laravel).

## 1. Integrasi Internal (Identity Server)

Digunakan jika Anda ingin melakukan penilaian risiko secara manual di dalam kode Identity Server.

### Layanan `AiRiskClientService`

Layanan ini dibungkus dalam interface `RiskAssessorInterface` dan mendukung penanganan kegagalan koneksi (fallback) secara otomatis.

```php
use App\Shared\Contracts\RiskAssessorInterface;

public function process(RiskAssessorInterface $riskAssessor)
{
    $payload = ['user_id' => 1, 'ip' => '127.0.0.1'];
    $result = $riskAssessor->assess($payload);
    
    if ($result->decision === 'BLOCK') {
        // Tindakan pemblokiran
    }
}
```

## 2. Integrasi Klien Laravel (`mixu/sso-auth`)

Jika Anda mengembangkan aplikasi klien menggunakan Laravel, disarankan menggunakan paket resmi untuk mendapatkan fitur keamanan aktif secara otomatis.

### Instalasi dan Prasyarat

Pastikan lingkungan Anda memenuhi syarat berikut:
- **PHP**: ^8.2
- **Composer**: ^2.0
- **Database**: Untuk menyimpan log aktivitas dan kejadian keamanan.

Instal paket melalui Composer:
```bash
composer require mixu/sso-auth
```

### Konfigurasi Keamanan

Pastikan Anda telah mempublikasikan aset dan menjalankan migrasi (lihat [Panduan Instalasi](../../sso/integration)). Tambahkan secret webhook untuk mendukung Global Logout:

```env
SSO_WEBHOOK_SECRET=your_shared_secret
```

### Penggunaan `SecurityMonitoringService`

Layanan ini tersedia dalam paket untuk memantau anomali di sisi klien secara real-time.

```php
use Mixu\SSOAuth\Services\SecurityMonitoringService;

public function check(SecurityMonitoringService $security)
{
    // Deteksi anomali untuk user tertentu
    $anomalies = $security->detectAnomalies(auth()->id());
    
    // Log kejadian keamanan kustom
    $security->logSecurityEvent([
        'event_type' => 'suspicious_access',
        'severity' => 'high',
        'details' => ['reason' => 'Multiple failed attempts']
    ]);
}
```

## Struktur Data `RiskAssessmentResult`

Baik pada layanan internal maupun paket klien, hasil penilaian risiko dikembalikan dalam format objek DTO yang konsisten:

| Properti | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `riskScore` | int | Skor risiko (0-100). |
| `decision` | string | Keputusan: `ALLOW`, `OTP`, atau `BLOCK`. |
| `reasonFlags` | array | Daftar alasan (misal: `new_device`, `geo_anomaly`). |
| `rawResponse` | array | Data mentah dari AI service. |
