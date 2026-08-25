<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergySavingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'action_taken',
        'kwh_saved_estimate',
        'cost_saved_idr',
        'logged_at',
    ];

    protected $casts = [
        'kwh_saved_estimate' => 'float',
        'cost_saved_idr' => 'float',
        'logged_at' => 'datetime',
    ];
}
