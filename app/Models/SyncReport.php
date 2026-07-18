<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_target',
        'local_table',
        'sync_run_at',
        'records_processed',
        'records_success',
        'records_failed',
        'error_logs',
        'run_by',
    ];

    protected $casts = [
        'sync_run_at' => 'datetime',
        'error_logs' => 'array',
    ];

    public function runBy()
    {
        return $this->belongsTo(User::class, 'run_by');
    }
}
