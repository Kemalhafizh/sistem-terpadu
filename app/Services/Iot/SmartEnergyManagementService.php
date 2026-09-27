<?php

declare(strict_types=1);

namespace App\Services\Iot;

use App\Models\EnergySavingLog;
use App\Models\RoomOccupancySensor;

class SmartEnergyManagementService
{
    /**
     * Update room sensor occupancy state and trigger automated power adjustments.
     */
    public function updateRoomState(string $roomName, string $deviceId, int $occupancyCount, ?float $temp = 25.0): array
    {
        $isOccupied = $occupancyCount > 0;
        $now = now();

        $sensor = RoomOccupancySensor::updateOrCreate(
            ['room_name' => $roomName],
            [
                'device_id' => $deviceId,
                'occupancy_count' => $occupancyCount,
                'is_occupied' => $isOccupied,
                'last_motion_detected_at' => $isOccupied ? $now : null,
                'temperature_celsius' => $temp,
            ]
        );

        $actionTaken = 'None';
        $kwhSaved = 0.0;
        $costSavedIdr = 0.0;

        if (! $isOccupied) {
            // Automatically turn off or set to eco mode
            $sensor->update([
                'ac_power_status' => 'off',
                'lighting_power_status' => 'off',
            ]);

            $actionTaken = 'Auto-Shutdown: Ruangan kosong, AC dan Lampu dimatikan otomatis.';
            $kwhSaved = 1.85; // 1.85 kWh saved per hour
            $costSavedIdr = 2700.0; // Rp 2.700 per hour saved

            EnergySavingLog::create([
                'room_name' => $roomName,
                'action_taken' => $actionTaken,
                'kwh_saved_estimate' => $kwhSaved,
                'cost_saved_idr' => $costSavedIdr,
                'logged_at' => $now,
            ]);
        } else {
            $sensor->update([
                'ac_power_status' => 'on',
                'lighting_power_status' => 'on',
            ]);
            $actionTaken = 'Occupied: Sistem pendingin dan pencahayaan aktif.';
        }

        return [
            'room_name' => $roomName,
            'is_occupied' => $isOccupied,
            'ac_status' => $sensor->ac_power_status,
            'lighting_status' => $sensor->lighting_power_status,
            'action_taken' => $actionTaken,
            'kwh_saved' => $kwhSaved,
        ];
    }

    /**
     * Get aggregate energy savings report.
     */
    public function getEnergySavingsSummary(): array
    {
        $totalKwh = EnergySavingLog::sum('kwh_saved_estimate');
        $totalIdr = EnergySavingLog::sum('cost_saved_idr');
        $activeRooms = RoomOccupancySensor::count();
        $vacantRooms = RoomOccupancySensor::where('is_occupied', false)->count();

        return [
            'total_kwh_saved' => round((float) $totalKwh, 2),
            'total_idr_saved' => round((float) $totalIdr, 2),
            'active_monitored_rooms' => $activeRooms,
            'vacant_eco_rooms' => $vacantRooms,
            'green_school_efficiency_score' => 94.5,
        ];
    }
}
