<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmnichannelTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_code',
        'subject',
        'body_content',
        'allowed_channels',
        'variables',
    ];

    protected $casts = [
        'allowed_channels' => 'array',
        'variables' => 'array',
    ];
}
