<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpsServerHealthLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_hostname',
        'cpu_usage_percent',
        'ram_usage_percent',
        'ram_used_mb',
        'ram_total_mb',
        'disk_usage_percent',
        'active_mysql_connections',
        'redis_pending_jobs',
        'response_time_ms',
        'health_status',
    ];

    protected $casts = [
        'cpu_usage_percent' => 'decimal:2',
        'ram_usage_percent' => 'decimal:2',
        'ram_used_mb' => 'decimal:2',
        'ram_total_mb' => 'decimal:2',
        'disk_usage_percent' => 'decimal:2',
        'response_time_ms' => 'decimal:2',
    ];
}
