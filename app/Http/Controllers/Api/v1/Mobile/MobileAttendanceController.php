<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Mobile;

use App\Http\Controllers\Controller;
use App\Services\Mobile\GeofenceAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MobileAttendanceController
 *
 * Manages mobile geolocation clock-in, anti-mock GPS enforcement,
 * and dynamic rotating TOTP QR token generation for contactless campus access.
 */
class MobileAttendanceController extends Controller
{
    public function __construct(
        protected GeofenceAttendanceService $geofenceService
    ) {}

    /**
     * Verify GPS coordinates against campus radius and record check-in.
     */
    public function gpsCheckin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy_meters' => 'required|numeric|min:0',
            'is_mock_location' => 'nullable|boolean',
            'selfie_proof_url' => 'nullable|string|url',
        ]);

        $result = $this->geofenceService->validateCampusCheckin(
            $request->user(),
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $validated['accuracy_meters'],
            (bool) ($validated['is_mock_location'] ?? false),
            $validated['selfie_proof_url'] ?? null
        );

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    /**
     * Generate Dynamic Rotating TOTP QR Token (valid for 30s) to display on student/staff phone.
     */
    public function generateDynamicQr(Request $request): JsonResponse
    {
        $qrData = $this->geofenceService->generateDynamicTotpQr($request->user());

        return response()->json($qrData, 200);
    }

    /**
     * Verify a scanned Dynamic TOTP QR Token at the gate attendance terminal.
     */
    public function verifyDynamicQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => 'required|string',
        ]);

        $result = $this->geofenceService->verifyDynamicTotpQr($validated['qr_token']);

        $statusCode = $result['valid'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
