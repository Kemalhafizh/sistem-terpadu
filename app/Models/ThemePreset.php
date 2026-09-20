<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemePreset extends Model
{
    use HasFactory;

    protected $table = 'theme_presets';

    protected $fillable = [
        'name',
        'slug',
        'token_overrides',
        'trigger_type',
        'trigger_config',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'token_overrides' => 'array',
        'trigger_config' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];
}
