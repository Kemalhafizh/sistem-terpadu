<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadershipPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'current_holder_id',
        'succession_priority',
        'review_date',
    ];

    protected $casts = [
        'review_date' => 'date',
    ];

    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(SuccessionCandidate::class);
    }
}
