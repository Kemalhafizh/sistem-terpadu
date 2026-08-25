<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomOccupancySensor extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'device_id',
        'occupancy_count',
        'is_occupied',
        'last_motion_detected_at',
        'ac_power_status',
        'lighting_power_status',
        'temperature_celsius',
    ];

    protected $casts = [
        'occupancy_count' => 'integer',
        'is_occupied' => 'boolean',
        'last_motion_detected_at' => 'datetime',
        'temperature_celsius' => 'float',
    ];
}
