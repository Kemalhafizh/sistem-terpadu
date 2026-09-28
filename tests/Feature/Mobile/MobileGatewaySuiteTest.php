<?php

namespace Tests\Feature\Mobile;

use App\Models\User;
use App\Models\UserDeviceToken;
use App\Services\Mobile\PushNotificationService;
use App\Support\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileGatewaySuiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_remote_config_returns_version_gate(): void
    {
        $response = $this->getJson('/api/v1/mobile/config?platform=android&build=102');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'app_name',
                'server_time',
                'maintenance' => ['is_maintenance', 'message'],
                'version_check' => [
                    'current_client_build',
                    'minimum_required_build',
                    'latest_available_build',
                    'update_required',
                    'update_available',
                    'store_url',
                ],
                'features',
                'contact_support',
            ]);
    }

    public function test_mobile_login_registers_device_and_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'student.mobile@sultanagung1.sch.id',
            'password' => bcrypt('secret123'),
            'role' => 'student',
        ]);

        $payload = [
            'email' => 'student.mobile@sultanagung1.sch.id',
            'password' => 'secret123',
            'device_id' => 'samsung-galaxy-s24-uuid-001',
            'device_name' => 'Samsung Galaxy S24 Ultra',
            'platform' => 'android',
            'fcm_token' => 'fcm_mock_token_abcdef123456',
            'os_version' => 'Android 15',
            'app_version' => '1.0.5',
        ];

        $response = $this->postJson('/api/v1/mobile/auth/login', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'code' => 'LOGIN_SUCCESS',
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('user_device_tokens', [
            'user_id' => $user->id,
            'device_id' => 'samsung-galaxy-s24-uuid-001',
            'platform' => 'android',
            'is_active' => true,
        ]);
    }

    public function test_biometric_auth_challenge_and_verification_flow(): void
    {
        $user = User::factory()->create([
            'role' => 'guru',
        ]);

        // 1. Request Nonce Challenge
        $challengeRes = $this->postJson('/api/v1/mobile/auth/biometric/challenge', [
            'device_id' => 'iphone-16-pro-uuid-002',
        ]);

        $challengeRes->assertStatus(200)
            ->assertJsonStructure(['success', 'nonce', 'expires_in']);

        // 2. Register Biometric Credential as Authenticated User
        $registerRes = $this->actingAs($user)->postJson('/api/v1/mobile/auth/biometric/register', [
            'device_id' => 'iphone-16-pro-uuid-002',
            'public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA',
            'biometric_type' => 'face_id',
        ]);

        $registerRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // 3. Verify Biometric Challenge without Password
        $verifyRes = $this->postJson('/api/v1/mobile/auth/biometric/verify', [
            'user_id' => $user->id,
            'device_id' => 'iphone-16-pro-uuid-002',
            'signature' => 'mock_biometric_hardware_signature',
        ]);

        $verifyRes->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertNotEmpty($verifyRes->json('token'));
    }

    public function test_user_can_list_and_revoke_active_devices(): void
    {
        $user = User::factory()->create(['role' => 'parent']);

        UserDeviceToken::create([
            'user_id' => $user->id,
            'device_id' => 'device-ipad-pro',
            'device_name' => 'iPad Pro 12.9',
            'platform' => 'ios',
            'fcm_token' => 'mock_apns_token_ipad',
            'is_active' => true,
        ]);

        $listRes = $this->actingAs($user)->getJson('/api/v1/mobile/devices');
        $listRes->assertStatus(200)
            ->assertJsonCount(1, 'devices');

        $revokeRes = $this->actingAs($user)->deleteJson('/api/v1/mobile/devices/device-ipad-pro');
        $revokeRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('user_device_tokens', [
            'device_id' => 'device-ipad-pro',
        ]);
    }

    public function test_push_notification_service_formats_fcm_and_apns_correctly(): void
    {
        $user = User::factory()->create();
        $pushService = app(PushNotificationService::class);

        $pushService->registerDeviceToken($user, [
            'device_id' => 'pixel-9-pro',
            'platform' => 'android',
            'fcm_token' => 'fcm_token_pixel9',
        ]);

        $result = $pushService->sendToUser(
            $user,
            'Tagihan SPP Tersedia',
            'Tagihan SPP bulan ini telah diterbitkan.',
            ['bill_id' => 123],
            'spp_billing'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['dispatched_count']);
        $this->assertEquals('spp_billing', $result['results'][0]['payload']['message']['android']['notification']['channel_id']);
    }

    public function test_gps_geofence_attendance_checkin(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        // 1. Outside Radius Checkin
        $outsideRes = $this->actingAs($user)->postJson('/api/v1/mobile/attendance/gps-checkin', [
            'latitude' => -6.900000, // far away
            'longitude' => 110.400000,
            'accuracy_meters' => 15.0,
            'is_mock_location' => false,
        ]);
        $outsideRes->assertStatus(422)
            ->assertJson(['code' => 'OUTSIDE_GEOFENCE_RADIUS']);

        // 2. Mock Location Rejected
        $mockRes = $this->actingAs($user)->postJson('/api/v1/mobile/attendance/gps-checkin', [
            'latitude' => School::campus()['latitude'],
            'longitude' => School::campus()['longitude'],
            'accuracy_meters' => 10.0,
            'is_mock_location' => true,
        ]);
        $mockRes->assertStatus(422)
            ->assertJson(['code' => 'MOCK_LOCATION_DETECTED']);

        // 3. Valid Checkin within Campus Coordinates
        $validRes = $this->actingAs($user)->postJson('/api/v1/mobile/attendance/gps-checkin', [
            'latitude' => School::campus()['latitude'],
            'longitude' => School::campus()['longitude'],
            'accuracy_meters' => 12.0,
            'is_mock_location' => false,
        ]);
        $validRes->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_dynamic_rotating_totp_qr_flow(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $qrGenRes = $this->actingAs($user)->getJson('/api/v1/mobile/attendance/dynamic-qr');
        $qrGenRes->assertStatus(200)
            ->assertJsonStructure(['success', 'qr_token', 'expires_in_seconds', 'timestamp']);

        $token = $qrGenRes->json('qr_token');

        // The student shows the QR; a teacher's phone scans it. A student
        // cannot act as the scanner.
        $this->actingAs($user)->postJson('/api/v1/mobile/attendance/verify-qr', ['qr_token' => $token])->assertForbidden();

        $scanner = User::factory()->create(['role' => 'guru']);
        $verifyRes = $this->actingAs($scanner)->postJson('/api/v1/mobile/attendance/verify-qr', [
            'qr_token' => $token,
        ]);

        $verifyRes->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'user_id' => $user->id,
            ]);
    }

    public function test_mobile_delta_sync_pull(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)
            ->withHeader('X-Device-ID', 'mobile-test-device-001')
            ->getJson('/api/v1/mobile/sync/pull?since='.urlencode(now()->subDays(7)->toIso8601String()));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'synced_at',
                'records_synced',
                'delta',
            ]);

        $this->assertDatabaseHas('mobile_sync_logs', [
            'user_id' => $user->id,
            'device_id' => 'mobile-test-device-001',
        ]);
    }

    public function test_well_known_manifests_for_android_and_ios_deep_linking(): void
    {
        // Android App Links
        $androidRes = $this->getJson('/.well-known/assetlinks.json');
        $androidRes->assertStatus(200);
        $this->assertEquals('android_app', $androidRes->json('0.target.namespace'));
        $this->assertEquals('sch.id.sultanagung1.sista', $androidRes->json('0.target.package_name'));

        // Apple Universal Links (AASA)
        $appleRes = $this->getJson('/.well-known/apple-app-site-association');
        $appleRes->assertStatus(200);
        $this->assertNotEmpty($appleRes->json('applinks.details.0.appID'));
    }
}
