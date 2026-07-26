<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPathwayEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_pathway_id',
        'enrolled_at',
        'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function pathway(): BelongsTo
    {
        return $this->belongsTo(AcademicPathway::class, 'academic_pathway_id');
    }
}
