<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation360Instrument extends Model
{
    use HasFactory;

    protected $table = 'evaluation_360_instruments';

    protected $fillable = [
        'cycle_id',
        'evaluator_type',
        'questions',
        'scale_type',
        'is_anonymous',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_anonymous' => 'boolean',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Evaluation360Cycle::class, 'cycle_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Evaluation360Response::class, 'instrument_id');
    }
}
