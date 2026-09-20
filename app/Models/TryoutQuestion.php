<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryoutQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tryout_id',
        'sub_test',
        'question_text',
        'options',
        'correct_answer',
        'difficulty_b',
        'discrimination_a',
    ];

    protected $casts = [
        'options' => 'array',
        'difficulty_b' => 'float',
        'discrimination_a' => 'float',
    ];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class, 'tryout_id');
    }
}
