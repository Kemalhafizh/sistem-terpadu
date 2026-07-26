<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation360Response extends Model
{
    use HasFactory;

    protected $table = 'evaluation_360_responses';

    protected $fillable = [
        'instrument_id',
        'evaluatee_id',
        'evaluator_id',
        'scores',
        'comments',
        'submitted_at',
    ];

    protected $casts = [
        'scores' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Evaluation360Instrument::class, 'instrument_id');
    }

    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
