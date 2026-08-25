<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EssaySubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'assignment_title',
        'essay_text',
        'ai_score',
        'originality_score',
        'argument_depth_score',
        'coherence_score',
        'ai_feedback',
        'plagiarism_matches',
    ];

    protected $casts = [
        'ai_score' => 'float',
        'originality_score' => 'float',
        'argument_depth_score' => 'float',
        'coherence_score' => 'float',
        'plagiarism_matches' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
