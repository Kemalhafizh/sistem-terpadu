<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserThemePreference extends Model
{
    use HasFactory;

    protected $table = 'user_theme_preferences';

    protected $fillable = [
        'user_id',
        'active_preset',
        'custom_overrides',
        'auto_theme',
        'dynamic_color',
    ];

    protected $casts = [
        'custom_overrides' => 'array',
        'auto_theme' => 'boolean',
        'dynamic_color' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
