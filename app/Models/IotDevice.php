<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IotDevice extends Model
{
    use HasFactory;

    protected $table = 'iot_devices';

    protected $fillable = [
        'device_name',
        'device_type',
        'location',
        'ip_address',
        'status',
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(IotSensorReading::class, 'device_id');
    }
}
