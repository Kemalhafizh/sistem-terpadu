<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use App\Services\Mobile\GeofenceAttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase78HardwareIntegrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dynamic_totp_qr_generate_and_verify(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/mobile/attendance/dynamic-qr');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'qr_token', 'expires_in_seconds', 'timestamp']);

        $token = $response->json('qr_token');
        $verifyRes = $this->actingAs($user, 'sanctum')->postJson('/api/v1/mobile/attendance/verify-qr', [
            'qr_token' => $token,
        ]);
        $verifyRes->assertStatus(200);
        $verifyRes->assertJsonPath('valid', true);
    }

    public function test_gps_geofence_haversine_calculation(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/mobile/attendance/gps-checkin', [
            'latitude' => GeofenceAttendanceService::CAMPUS_LATITUDE,
            'longitude' => GeofenceAttendanceService::CAMPUS_LONGITUDE,
            'accuracy_meters' => 10.0,
            'is_mock_location' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}
