<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemedialSession extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
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

    public function assessment()
    {
        return $this->belongsTo(DailyAssessment::class, 'daily_assessment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
