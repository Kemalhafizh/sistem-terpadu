<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormTracking extends Model
{
    use HasFactory;

    protected $table = 'scorm_tracking';

    protected $fillable = [
        'scorm_package_id',
        'student_id',
        'lesson_status',
        'score_raw',
        'score_max',
        'total_time_seconds',
        'suspend_data',
        'last_accessed_at',
    ];

    protected $casts = [
        'score_raw' => 'float',
        'score_max' => 'float',
        'last_accessed_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(ScormPackage::class, 'scorm_package_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
