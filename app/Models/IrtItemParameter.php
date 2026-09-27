<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrtItemParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'difficulty',
        'discrimination',
        'guessing',
        'standard_error',
        'total_attempts',
        'correct_count',
        'p_value',
        'last_calibrated_at',
    ];

    protected $casts = [
        'difficulty' => 'float',
        'discrimination' => 'float',
        'guessing' => 'float',
        'standard_error' => 'float',
        'total_attempts' => 'integer',
        'correct_count' => 'integer',
        'p_value' => 'float',
        'last_calibrated_at' => 'datetime',
    ];

    /** The CBT bank item (question_banks) this row is about. */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}
