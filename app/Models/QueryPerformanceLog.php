<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueryPerformanceLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'route',
        'query_sql',
        'execution_time_ms',
        'bindings',
        'is_slow',
        'stack_trace',
        'created_at',
    ];

    protected $casts = [
        'execution_time_ms' => 'float',
        'bindings' => 'array',
        'is_slow' => 'boolean',
        'created_at' => 'datetime',
    ];
}
