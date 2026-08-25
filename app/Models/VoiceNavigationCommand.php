<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoiceNavigationCommand extends Model
{
    use HasFactory;

    protected $table = 'voice_navigation_commands';

    protected $fillable = [
        'language_code',
        'command_phrase',
        'action_url',
        'target_module',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
