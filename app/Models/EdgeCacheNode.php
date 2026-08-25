<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdgeCacheNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_location',
        'edge_ip',
        'cache_hit_ratio',
        'bandwidth_mbps',
        'cached_assets_count',
        'hls_stream_active',
        'status',
    ];

    protected $casts = [
        'cache_hit_ratio' => 'float',
        'bandwidth_mbps' => 'float',
        'cached_assets_count' => 'integer',
        'hls_stream_active' => 'boolean',
    ];
}
