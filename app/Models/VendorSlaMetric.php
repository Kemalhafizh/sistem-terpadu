<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSlaMetric extends Model
{
    use HasFactory;

    protected $table = 'vendor_sla_metrics';

    protected $fillable = [
        'vendor_key',
        'vendor_name',
        'endpoint_url',
        'status',
        'latency_ms',
        'http_status',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'latency_ms' => 'float',
        'http_status' => 'integer',
        'checked_at' => 'datetime',
    ];
}
