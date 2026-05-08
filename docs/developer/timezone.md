# Timezone Management

Sistem ini menyimpan semua data waktu di database dalam format **UTC**. Modul Timezone bertanggung jawab untuk mendeteksi zona waktu user secara otomatis dan menampilkan waktu yang sesuai di sisi interface (View).

## Cara Kerja

1. **Deteksi Otomatis**: Saat user login atau mengakses halaman dashboard, sebuah script ringan (`Intl.DateTimeFormat`) akan mendeteksi zona waktu browser mereka.
2. **Persistence**: Zona waktu yang terdeteksi dikirim ke backend via AJAX dan disimpan di sesi user serta kolom `timezone` pada tabel `users`.
3. **Middleware**: `TimezoneMiddleware` akan memastikan zona waktu aplikasi diatur sesuai dengan preferensi user pada setiap request.

## Penggunaan di Blade (Directives)

Kami menyediakan beberapa *Blade directives* untuk memudahkan konversi waktu di file `.blade.php`:

### `@localtime`
Menampilkan waktu lokal user dengan format default (`d M Y, H:i`).
```blade
@localtime($user->created_at)
// Output: 21 Mar 2026, 18:30
```

### `@localtimef`
Menampilkan waktu lokal dengan format kustom.
```blade
@localtimef($post->published_at, 'l, d F Y')
// Output: Sabtu, 21 Maret 2026
```

### `@localdate`
Hanya menampilkan tanggal lokal.
```blade
@localdate($invoice->due_date)
// Output: 21 Mar 2026
```

### `@localtime_only`
Hanya menampilkan jam lokal.
```blade
@localtime_only($log->created_at)
// Output: 18:30
```

### `@humanstime`
Menampilkan waktu relatif (diff for humans) yang sudah disesuaikan dengan timezone lokal.
```blade
@humanstime($comment->created_at)
// Output: 5 menit yang lalu
```

### `@timezone`
Menampilkan nama timezone yang sedang aktif digunakan oleh user.
```blade
<span>Zona Waktu Anda: @timezone</span>
// Output: Asia/Jakarta
```

## Penggunaan di PHP (Carbon Macros)

Anda juga bisa menggunakan Carbon macros jika ingin melakukan manipulasi waktu di Controller atau Service:

### `toLocal()`
Mengonversi objek Carbon ke timezone lokal user.
```php
$localTime = $model->created_at->toLocal();
```

### `localFormat()`
Mengonversi dan langsung memformat waktu ke string.
```php
$string = $model->created_at->localFormat('d/m/Y H:i');
```

## Injeksi Script Deteksi

Jika Anda membuat layout baru, pastikan untuk menyertakan directive berikut di bagian bawah sebelum tag `</body>` untuk memastikan deteksi timezone tetap berjalan:

```blade
@localscript
```
