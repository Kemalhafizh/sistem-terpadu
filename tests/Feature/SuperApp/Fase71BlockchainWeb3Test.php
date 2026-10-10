<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase71BlockchainWeb3Test extends TestCase
{
    use RefreshDatabase;

    public function test_issue_and_verify_blockchain_credential(): void
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Ahmad Dahlan']);
        $registrar = User::factory()->create(['role' => 'staf_tu']);

        // A student cannot mint their own diploma.
        $this->actingAs($student, 'sanctum')->postJson('/api/v1/blockchain/credentials/issue', [
            'student_id' => $student->id,
            'credential_type' => 'diploma',
            'certificate_number' => 'DIPLOMA-SELF-ISSUED',
            'metadata' => ['gpa' => 4.0],
        ])->assertForbidden();

        // 1. The school issues the credential to the student
        $issueRes = $this->actingAs($registrar, 'sanctum')->postJson('/api/v1/blockchain/credentials/issue', [
            'student_id' => $student->id,
            'credential_type' => 'diploma',
            'certificate_number' => 'DIPLOMA-YBWSA-2026-00981',
            'metadata' => [
                'major' => 'MIPA',
                'gpa' => 3.92,
                'tahfidz_juz' => 30,
            ],
        ]);

        $issueRes->assertStatus(201);
        $merkleRoot = $issueRes->json('credential.merkle_root');
        $this->assertStringStartsWith('0x', $merkleRoot);

        // 2. Public Verification without auth
        $verifyRes = $this->getJson('/api/v1/blockchain/credentials/verify/DIPLOMA-YBWSA-2026-00981');
        $verifyRes->assertStatus(200);
        $verifyRes->assertJsonPath('verified', true);
        $verifyRes->assertJsonPath('status', 'valid');
        $verifyRes->assertJsonPath('recipient_name', 'Ahmad Dahlan');
    }
}
