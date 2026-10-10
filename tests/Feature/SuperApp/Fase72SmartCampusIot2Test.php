<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase72SmartCampusIot2Test extends TestCase
{
    use RefreshDatabase;

    public function test_facial_recognition_enrollment_and_gate_matching(): void
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Muhammad Iqbal']);

        // Create 512-dimensional embedding vector
        $vector = array_fill(0, 512, 0.15);

        // 1. Enroll Profile
        $enrollRes = $this->actingAs($student, 'sanctum')->postJson('/api/v1/iot/face/enroll', [
            'embedding_vector' => $vector,
        ]);
        $enrollRes->assertStatus(200);
        $enrollRes->assertJsonPath('success', true);

        // 2. Match at Gate Terminal with close vector. Only the gate's
        // operator account may log attendance; a student may not.
        $capturedVector = array_fill(0, 512, 0.151); // very close Euclidean distance
        $gate = [
            'gate_camera_id' => 'CAM-GATE-NORTH-01',
            'gate_location' => 'Gerbang Utama Sultan Agung',
            'captured_vector' => $capturedVector,
            'liveness_verified' => true,
        ];
        $this->actingAs($student, 'sanctum')->postJson('/api/v1/iot/face/match', $gate)->assertForbidden();

        $gateOperator = User::factory()->create(['role' => 'operator']);
        $matchRes = $this->actingAs($gateOperator, 'sanctum')->postJson('/api/v1/iot/face/match', [
            'gate_camera_id' => 'CAM-GATE-NORTH-01',
            'gate_location' => 'Gerbang Utama Sultan Agung',
            'captured_vector' => $capturedVector,
            'liveness_verified' => true,
        ]);

        $matchRes->assertStatus(200);
        $matchRes->assertJsonPath('success', true);
        $matchRes->assertJsonPath('user.name', 'Muhammad Iqbal');
    }
}
