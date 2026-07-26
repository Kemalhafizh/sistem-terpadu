<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class E2eTestRun extends Model
{
    use HasFactory;

    protected $table = 'e2e_test_runs';

    protected $fillable = [
        'suite_name',
        'browser',
        'total_tests',
        'passed',
        'failed',
        'skipped',
        'duration_seconds',
        'status',
        'triggered_by',
        'started_at',
        'completed_at',
        'report_path',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
