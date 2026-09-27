<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatItemResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_session_id',
        'question_id',
        'sequence_number',
        'is_correct',
        'theta_before',
        'theta_after',
        'item_difficulty',
        'information_value',
        'response_time_seconds',
    ];

    protected $casts = [
        'sequence_number' => 'integer',
        'is_correct' => 'boolean',
        'theta_before' => 'float',
        'theta_after' => 'float',
        'item_difficulty' => 'float',
        'information_value' => 'float',
        'response_time_seconds' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(CatSession::class, 'cat_session_id');
    }

    /** The CBT bank item (question_banks) this row is about. */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}
