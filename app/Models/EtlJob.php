<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EtlJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'source_type',
        'source_file_path',
        'target_table',
        'mapping_config',
        'validation_errors',
        'status',
        'records_total',
        'records_processed',
        'records_failed',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'mapping_config' => 'array',
        'validation_errors' => 'array',
        'records_total' => 'integer',
        'records_processed' => 'integer',
        'records_failed' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->records_total === 0) {
            return 0.0;
        }

        return round(($this->records_processed / $this->records_total) * 100, 1);
    }
}
