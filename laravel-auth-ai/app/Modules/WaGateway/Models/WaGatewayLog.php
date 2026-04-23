<?php

namespace App\Modules\WaGateway\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WaGatewayLog extends Model
{
    use HasFactory;

    protected $table = 'wa_gateway_logs';

    protected $fillable = [
        'wa_gateway_config_id',
        'target_number',
        'message',
        'status',
        'response_id',
        'response_data',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'response_data' => 'array',
        'sent_at' => 'datetime',
    ];

    public function config()
    {
        return $this->belongsTo(WaGatewayConfig::class, 'wa_gateway_config_id');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
