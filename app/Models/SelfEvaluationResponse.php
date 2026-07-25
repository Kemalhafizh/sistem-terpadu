<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfEvaluationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id',
        'standard_id',
        'question_text',
        'response_text',
        'score',
        'evidence_notes',
        'respondent_id',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(SelfEvaluationCycle::class, 'cycle_id');
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(AccreditationStandard::class, 'standard_id');
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }
}
