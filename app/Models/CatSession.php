<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'student_id',
        'exam_id',
        'current_theta',
        'theta_se',
        'items_administered',
        'correct_count',
        'status',
        'termination_rule',
        'max_items',
        'se_threshold',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'current_theta' => 'float',
        'theta_se' => 'float',
        'items_administered' => 'integer',
        'correct_count' => 'integer',
        'max_items' => 'integer',
        'se_threshold' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function responses()
    {
        return $this->hasMany(CatItemResponse::class);
    }
}
