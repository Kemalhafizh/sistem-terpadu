<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryoutAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'tryout_id',
        'student_id',
        'score_tps',
        'score_literasi',
        'score_penalaran',
        'total_score',
        'started_at',
        'completed_at',
        'answers_json',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers_json' => 'array',
        'score_tps' => 'float',
        'score_literasi' => 'float',
        'score_penalaran' => 'float',
        'total_score' => 'float',
    ];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class, 'tryout_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
