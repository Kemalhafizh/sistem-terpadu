<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'likelihood',
        'impact',
        'mitigation_plan',
        'owner_id',
        'status',
        'review_date',
    ];

    protected $casts = [
        'review_date' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getRiskScoreAttribute(): int
    {
        $likelihoodMap = [
            'rare' => 1,
            'unlikely' => 2,
            'possible' => 3,
            'likely' => 4,
            'almost_certain' => 5,
        ];

        $impactMap = [
            'insignificant' => 1,
            'minor' => 2,
            'moderate' => 3,
            'major' => 4,
            'catastrophic' => 5,
        ];

        $l = $likelihoodMap[$this->likelihood] ?? 1;
        $i = $impactMap[$this->impact] ?? 1;

        return $l * $i;
    }

    public function getRiskLevelAttribute(): string
    {
        $score = $this->risk_score;

        return match (true) {
            $score >= 15 => 'Extreme',
            $score >= 10 => 'High',
            $score >= 5 => 'Medium',
            default => 'Low',
        };
    }
}
