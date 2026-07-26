<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeritPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'awarded_by',
        'character_dimension_id',
        'points',
        'reason',
        'event_context',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function awarder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(CharacterDimension::class, 'character_dimension_id');
    }
}
