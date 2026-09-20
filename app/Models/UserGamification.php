<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGamification extends Model
{
    use HasFactory;

    protected $table = 'user_gamification';

    protected $fillable = [
        'user_id',
        'total_xp',
        'level',
        'level_title',
        'current_streak_days',
        'longest_streak_days',
        'last_active_date',
        'badges_earned',
        'monthly_rank',
    ];

    protected $casts = [
        'total_xp' => 'integer',
        'level' => 'integer',
        'current_streak_days' => 'integer',
        'longest_streak_days' => 'integer',
        'last_active_date' => 'date',
        'badges_earned' => 'array',
        'monthly_rank' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class, 'user_id', 'user_id');
    }
}
