<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEvaluationResponse extends Model
{
    use HasFactory;

    protected $table = 'course_evaluation_responses';

    protected $fillable = [
        'evaluation_id',
        'student_id',
        'rating',
        'comments',
        'is_anonymous',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(CourseEvaluation::class, 'evaluation_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
