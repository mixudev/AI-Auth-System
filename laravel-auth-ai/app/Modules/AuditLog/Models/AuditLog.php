<?php

namespace App\Modules\AuditLog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog Model
 * 
 * Digunakan untuk mencatat riwayat aktivitas pengguna/admin di seluruh sistem.
 * Mencatat informasi seperti siapa, melakukan apa (event), data lama, data baru, 
 * lokasi URL, alamat IP, dan user agent perangkat yang digunakan.
 */
class AuditLog extends Model
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'audit_logs';

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
    ];

    /**
     * Konversi tipe data otomatis (casting).
     */
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    /**
     * Relasi ke User: Memberitahu sistem siapa yang melakukan aksi ini.
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
