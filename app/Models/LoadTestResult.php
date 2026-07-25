<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_name',
        'concurrent_users',
        'duration_seconds',
        'total_requests',
        'requests_per_second',
        'p50_latency_ms',
        'p95_latency_ms',
        'p99_latency_ms',
        'error_rate_percent',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'concurrent_users' => 'integer',
        'duration_seconds' => 'integer',
        'total_requests' => 'integer',
        'requests_per_second' => 'float',
        'p50_latency_ms' => 'float',
        'p95_latency_ms' => 'float',
        'p99_latency_ms' => 'float',
        'error_rate_percent' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
