<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use App\Support\School;
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
        $response->assertJsonStructure(['success', 'qr_token', 'qr_payload', 'expires_in_seconds', 'timestamp', 'generated_at']);

        // Contract of Android's DynamicQrResponse(qr_payload: String, expires_in_seconds: Int, timestamp: Long).
        expect($response->json('qr_payload'))->toBe($response->json('qr_token'))
            ->and($response->json('timestamp'))->toBeInt()
            ->and($response->json('expires_in_seconds'))->toBeInt()->toBeBetween(1, 30);

        $token = $response->json('qr_token');
        $scanner = User::factory()->create(['role' => 'staf_tu']);
        $verifyRes = $this->actingAs($scanner, 'sanctum')->postJson('/api/v1/mobile/attendance/verify-qr', [
            'qr_token' => $token,
        ]);
        $verifyRes->assertStatus(200);
        $verifyRes->assertJsonPath('valid', true);
    }

    public function test_gps_geofence_haversine_calculation(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/mobile/attendance/gps-checkin', [
            'latitude' => School::campus()['latitude'],
            'longitude' => School::campus()['longitude'],
            'accuracy_meters' => 10.0,
            'is_mock_location' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}
