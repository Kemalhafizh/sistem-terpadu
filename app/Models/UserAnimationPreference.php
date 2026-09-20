<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnimationPreference extends Model
{
    use HasFactory;

    protected $table = 'user_animation_preferences';

    protected $fillable = [
        'user_id',
        'reduce_motion',
        'haptic_level',
        'skeleton_loading',
        'spring_animations',
        'parallax_effects',
        'page_transitions',
        'pull_refresh_style',
    ];

    protected $casts = [
        'reduce_motion' => 'boolean',
        'skeleton_loading' => 'boolean',
        'spring_animations' => 'boolean',
        'parallax_effects' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
