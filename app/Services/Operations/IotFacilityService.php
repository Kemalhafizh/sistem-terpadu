<?php

namespace App\Services\Operations;

use App\Models\IotDevice;
use App\Models\IotSensorReading;

class IotFacilityService
{
    public function registerDevice(string $name, string $type, string $location, ?string $ip = null): IotDevice
    {
        return IotDevice::create([
            'device_name' => $name,
            'device_type' => $type,
            'location' => $location,
            'ip_address' => $ip,
            'status' => 'online',
        ]);
    }

    public function recordReading(int $deviceId, float $value, string $unit): IotSensorReading
    {
        return IotSensorReading::create([
            'device_id' => $deviceId,
            'metric_value' => $value,
            'metric_unit' => $unit,
            'read_at' => now(),
        ]);
    }
}
