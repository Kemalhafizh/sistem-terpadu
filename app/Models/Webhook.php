<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'failure_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
    ];
}
