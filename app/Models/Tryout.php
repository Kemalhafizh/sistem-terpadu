<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(TryoutQuestion::class, 'tryout_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TryoutAttempt::class, 'tryout_id');
    }
}
