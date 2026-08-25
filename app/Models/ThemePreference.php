<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemePreference extends Model
{
    use HasFactory;

    protected $table = 'theme_preferences';

    protected $fillable = [
        'user_id',
        'color_mode',
        'font_scaling',
        'reduce_animations',
        'dyslexic_font_enabled',
        'primary_color_hex',
    ];

    protected $casts = [
        'reduce_animations' => 'boolean',
        'dyslexic_font_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
