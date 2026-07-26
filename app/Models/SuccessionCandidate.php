<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuccessionCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'leadership_position_id',
        'candidate_id',
        'readiness_level',
        'strengths',
        'development_areas',
        'development_plan',
        'endorsed_by',
    ];

    protected $casts = [
        'strengths' => 'array',
        'development_areas' => 'array',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(LeadershipPosition::class, 'leadership_position_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function endorser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }
}
