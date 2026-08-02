<?php

namespace App\Services\Operations;

use App\Models\SchoolBus;
use App\Models\BusRoute;

class BusRoutingService
{
    public function registerBus(string $plate, string $driverName, string $driverPhone, int $capacity = 30): SchoolBus
    {
        return SchoolBus::create([
            'plate_number' => $plate,
            'driver_name' => $driverName,
            'driver_phone' => $driverPhone,
            'capacity' => $capacity,
            'is_active' => true,
        ]);
    }

    public function createRoute(int $busId, string $routeName, string $depTime = '06:00', string $arrTime = '06:45'): BusRoute
    {
        return BusRoute::create([
            'bus_id' => $busId,
            'route_name' => $routeName,
            'departure_time' => $depTime,
            'arrival_time' => $arrTime,
        ]);
    }
}
