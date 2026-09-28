<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RemedialSession extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $guarded = [];

    protected $casts = [
        'original_score' => 'decimal:2',
        'kkm' => 'decimal:2',
        'remedial_score' => 'decimal:2',
        'deadline' => 'datetime',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(DailyAssessment::class, 'daily_assessment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
