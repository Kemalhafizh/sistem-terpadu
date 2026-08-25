<?php

declare(strict_types=1);

namespace App\Http\Controllers\Iot;

use App\Http\Controllers\Controller;
use App\Models\RoomOccupancySensor;
use App\Services\Iot\SmartEnergyManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnergyManagementController extends Controller
{
    public function __construct(
        protected SmartEnergyManagementService $energyService
    ) {}

    public function updateSensor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_name' => 'required|string|max:128',
            'device_id' => 'required|string|max:64',
            'occupancy_count' => 'required|integer|min:0',
            'temperature' => 'nullable|numeric',
        ]);

        $result = $this->energyService->updateRoomState(
            $validated['room_name'],
            $validated['device_id'],
            (int)$validated['occupancy_count'],
            isset($validated['temperature']) ? (float)$validated['temperature'] : 25.0
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    public function getDashboard(): JsonResponse
    {
        $summary = $this->energyService->getEnergySavingsSummary();
        $rooms = RoomOccupancySensor::all();

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'rooms' => $rooms,
        ], 200);
    }
}
