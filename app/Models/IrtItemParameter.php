<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
