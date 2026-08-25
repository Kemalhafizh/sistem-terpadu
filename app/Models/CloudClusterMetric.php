<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudClusterMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_name',
        'cluster_zone',
        'cpu_usage_percent',
        'memory_usage_percent',
        'active_pod_replicas',
        'requests_per_second',
        'p99_latency_ms',
        'health_status',
    ];

    protected $casts = [
        'cpu_usage_percent' => 'float',
        'memory_usage_percent' => 'float',
        'active_pod_replicas' => 'integer',
        'requests_per_second' => 'integer',
        'p99_latency_ms' => 'float',
    ];
}
