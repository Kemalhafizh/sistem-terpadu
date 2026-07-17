<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthScreeningResult extends Model
{
    use HasFactory;

    public $timestamps = false; // No timestamps defined in migration

    protected $fillable = [
        'screening_id',
        'student_id',
        'height_cm',
        'weight_kg',
        'vision_left',
        'vision_right',
        'dental_status',
        'findings',
        'recommendations',
    ];

    protected $casts = [
        'height_cm' => 'decimal:1',
        'weight_kg' => 'decimal:1',
    ];

    public function screening(): BelongsTo
    {
        return $this->belongsTo(HealthScreening::class, 'screening_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
