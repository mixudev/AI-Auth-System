<?php

namespace App\Modules\SSO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SsoClient extends Model
{
    protected $fillable = [
        'name',
        'oauth_client_id',
        'webhook_url',
        'webhook_secret',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'webhook_secret',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate webhook secret baru (32 byte hex = 64 chars).
     */
    public static function generateWebhookSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
}
