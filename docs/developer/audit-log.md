# Audit Logging

Sistem Audit Log digunakan untuk mencatat setiap aktivitas penting yang terjadi di dalam aplikasi. Data ini sangat berguna untuk keperluan audit keamanan dan pelacakan perubahan data.

## Implementasi Otomatis (Middleware)

Cara termudah untuk mencatat aktivitas adalah dengan menggunakan `AuditMiddleware`. Middleware ini secara otomatis mencatat request yang masuk (kecuali metode `GET`) ke tabel `audit_logs`.

### Penggunaan di Routes
Tambahkan middleware `audit` pada route atau group route yang ingin dipantau:

```php
Route::middleware(['auth', 'audit'])->group(function () {
    Route::post('/settings/update', [ConfigurationController::class, 'update']);
});
```

### Event Kustom melalui Middleware
Anda bisa menentukan nama event kustom dengan memberikan parameter pada middleware:

```php
Route::post('/critical-action', [ActionController::class, 'execute'])
    ->middleware('audit:PERUBAHAN_SENSITIF');
```

## Implementasi Manual (Model)

Jika Anda perlu mencatat log secara manual di dalam Controller atau Service (misalnya untuk mencatat log pada metode `GET` atau logika bisnis tertentu), Anda bisa menggunakan model `AuditLog` secara langsung.

```php
use App\Modules\AuditLog\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

AuditLog::create([
    'user_id'    => Auth::id(),
    'event'      => 'AKSI_KUSTOM',
    'url'        => request()->fullUrl(),
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'new_values' => ['key' => 'value'], // Data yang ingin dicatat
    'old_values' => ['key' => 'old_value'], // Data sebelum perubahan (opsional)
]);
```

## Struktur Data Log

Setiap entry log mencatat informasi berikut:
- **User ID**: Siapa yang melakukan aksi.
- **Event**: Nama aksi (misal: `POST: settings/update` atau `AKSI_KUSTOM`).
- **URL**: URL lengkap saat aksi dilakukan.
- **IP Address & User Agent**: Informasi perangkat dan lokasi jaringan user.
- **Old & New Values**: JSON object yang berisi perbedaan data sebelum dan sesudah aksi.
