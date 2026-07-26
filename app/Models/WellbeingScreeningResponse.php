<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WellbeingScreeningResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'wellbeing_screening_id',
        'student_id',
        'responses',
        'total_score',
        'risk_level',
        'screened_at',
        'followup_required',
        'followup_notes',
        'counselor_id',
    ];

    protected $casts = [
        'responses' => 'array',
        'screened_at' => 'datetime',
        'followup_required' => 'boolean',
    ];

    public function screening(): BelongsTo
    {
        return $this->belongsTo(WellbeingScreening::class, 'wellbeing_screening_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
