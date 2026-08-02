<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotSensorReading extends Model
{
    use HasFactory;

    protected $table = 'iot_sensor_readings';

    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'metric_value',
        'metric_unit',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IotDevice::class, 'device_id');
    }
}
