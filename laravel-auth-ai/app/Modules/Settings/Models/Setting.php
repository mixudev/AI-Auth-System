<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Setting Model
 * 
 * Pusat pengaturan sistem (Global Settings). 
 * Menggunakan pendekatan Key-Value agar sangat fleksibel tanpa perlu mengubah struktur database
 * saat menambahkan fitur baru. Dilengkapi dengan caching otomatis untuk performa tinggi.
 */
class Setting extends Model
{
    /**
     * Nama tabel.
     */
    protected $table = 'settings';

    /**
     * Kolom yang dapat diisi.
     */
    protected $fillable = ['key', 'value', 'group', 'type', 'label', 'description'];

    /**
     * Mengambil nilai pengaturan berdasarkan key.
     * Menggunakan Cache::remember agar tidak selalu memukul database.
     * 
     * @param string $key Identifier unik pengaturan.
     * @param mixed $default Nilai default jika key tidak ditemukan.
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = Cache::rememberForever("setting.{$key}", function () use ($key) {
            return self::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Menyimpan atau memperbarui nilai pengaturan.
     * Otomatis menghapus cache setelah update.
     * 
     * @param string $key Identifier unik.
     * @param mixed $value Nilai yang akan disimpan.
     * @param string $group Pengelompokan (general, mail, sso, dll).
     * @param string $type Tipe data untuk casting (string, boolean, integer, json).
     * @return self
     */
    public static function set($key, $value, $group = 'general', $type = 'string')
    {
        // Enkripsi value jika tipe adalah 'encrypted'
        if ($type === 'encrypted' && !empty($value)) {
            $value = \Illuminate\Support\Facades\Crypt::encryptString($value);
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );

        // Hapus cache agar data terbaru segera terbaca
        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Helper untuk mengubah tipe data value (Casting).
     * 
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, $type)
    {
        if (empty($value)) return $value;

        switch ($type) {
            case 'encrypted':
                try {
                    return \Illuminate\Support\Facades\Crypt::decryptString($value);
                } catch (\Exception $e) {
                    return $value; // Return as-is if decryption fails
                }
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
            case 'int':
                return (int) $value;
            case 'json':
            case 'array':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
}
