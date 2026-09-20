<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignToken extends Model
{
    use HasFactory;

    protected $table = 'design_tokens';

    protected $fillable = [
        'token_key',
        'category',
        'value',
        'description',
        'is_customizable',
    ];

    protected $casts = [
        'value' => 'array',
        'is_customizable' => 'boolean',
    ];
}
