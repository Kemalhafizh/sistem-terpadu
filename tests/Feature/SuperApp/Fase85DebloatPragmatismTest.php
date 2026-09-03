<?php

namespace Tests\Feature\SuperApp;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\DigitalVerification;
use App\Models\Grade;
use App\Models\OfflineSyncQueue;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Fase85DebloatPragmatismTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;
    protected Student $student;
    protected Classroom $classroom;
    protected \App\Models\AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = \App\Models\AcademicYear::create([
            'year' => '2025/2026',
            'semester' => 'Ganjil',
            'is_active' => true,
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
        ]);

        $this->studentUser = User::factory()->create([
            'name' => 'Ahmad Kemal Siswa',
            'email' => 'siswa.kemal@student.sa1.sch.id',
            'role' => 'student',
        ]);

        $this->classroom = Classroom::factory()->create(['name' => 'XII MIPA 1']);

        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $this->classroom->id,
            'nisn' => '0069911223',
        ]);
    }

    /**
     * Test 85.1: Pragmatic Digital Certificate Verification without Blockchain.
     */
    public function test_issue_and_verify_digital_certificate_without_blockchain(): void
    {
        Sanctum::actingAs($this->studentUser);

        // 1. Issue certificate
        $issueResponse = $this->postJson('/api/v1/debloat/certificates/issue', [
            'student_id' => $this->student->id,
            'document_type' => 'IJAZAH',
            'document_number' => 'IJZ-2026-SMA-089',
        ]);

        $issueResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.document_number', 'IJZ-2026-SMA-089')
            ->assertJsonPath('data.student_nisn', '0069911223');

        $this->assertDatabaseHas('digital_verifications', [
            'document_number' => 'IJZ-2026-SMA-089',
            'document_type' => 'IJAZAH',
            'is_valid' => true,
        ]);

        // 2. Public verification without login
        $verifyResponse = $this->getJson('/api/v1/certificates/verify/IJZ-2026-SMA-089');

        $verifyResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_valid', true)
            ->assertJsonPath('data.document_number', 'IJZ-2026-SMA-089')
            ->assertJsonPath('data.student_name', 'Ahmad Kemal Siswa');

        // 3. Non-existent document
        $invalidResponse = $this->getJson('/api/v1/certificates/verify/NON-EXISTENT-999');
        $invalidResponse->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /**
     * Test 85.2: Pragmatic Rules-Based At-Risk Student Evaluation.
     */
    public function test_pragmatic_rules_based_at_risk_student_evaluation(): void
    {
        Sanctum::actingAs($this->studentUser);

        // Scenario 1: Clean student -> No alert
        $cleanResponse = $this->getJson("/api/v1/debloat/students/{$this->student->id}/at-risk");
        $cleanResponse->assertStatus(200)
            ->assertJsonPath('has_alert', false);

        // Scenario 2: Student accumulates 4 unexcused absences and 55 violation points
        for ($i = 0; $i < 4; $i++) {
            Attendance::create([
                'student_id' => $this->student->id,
                'academic_year_id' => $this->academicYear->id,
                'classroom_id' => $this->classroom->id,
                'date' => now()->subDays($i + 1)->toDateString(),
                'status' => 'A',
                'notes' => 'Tanpa Keterangan',
                'recorded_by' => $this->studentUser->id,
            ]);
        }

        StudentPointLog::create([
            'student_id' => $this->student->id,
            'type' => 'penalty',
            'points' => 55,
            'description' => 'Akumulasi keterlambatan dan pelanggaran atribut',
            'created_by' => $this->studentUser->id,
        ]);

        $riskResponse = $this->getJson("/api/v1/debloat/students/{$this->student->id}/at-risk");
        $riskResponse->assertStatus(200)
            ->assertJsonPath('has_alert', true)
            ->assertJsonPath('data.risk_level', 'HIGH')
            ->assertJsonPath('data.unexcused_absences', 4)
            ->assertJsonPath('data.violation_points', 55);

        $this->assertDatabaseHas('at_risk_alerts', [
            'student_id' => $this->student->id,
            'status' => 'OPEN',
            'risk_level' => 'HIGH',
        ]);
    }

    /**
     * Test 85.3: Single VPS Server Health Monitoring.
     */
    public function test_single_vps_server_health_metrics(): void
    {
        $response = $this->getJson('/api/v1/debloat/vps/health');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'architecture',
                    'status',
                    'cpu_percent',
                    'ram_percent',
                    'ram_used_mb',
                    'disk_percent',
                    'monthly_cost_estimate',
                    'recommended_spec',
                ],
            ]);

        $this->assertDatabaseHas('vps_server_health_logs', [
            'health_status' => 'HEALTHY',
        ]);
    }

    /**
     * Test 85.4: Pragmatic FIFO Offline Sync Queue & Quick Pull.
     */
    public function test_pragmatic_fifo_offline_sync_queue_and_quick_pull(): void
    {
        Sanctum::actingAs($this->studentUser);

        // 1. Process Actions Queue
        $clientUuid1 = 'uuid-action-attendance-001';
        $clientUuid2 = 'uuid-action-mutabaah-002';

        $syncResponse = $this->postJson('/api/v1/debloat/sync/queue', [
            'actions' => [
                [
                    'client_uuid' => $clientUuid1,
                    'action_type' => 'ATTENDANCE_CHECKIN',
                    'payload' => [
                        'date' => now()->toDateString(),
                        'status' => 'PRESENT',
                        'notes' => 'Presensi Offline Berhasil',
                    ],
                ],
                [
                    'client_uuid' => $clientUuid2,
                    'action_type' => 'MUTABAAH_LOG',
                    'payload' => [
                        'surah_name' => 'Al-Mulk',
                        'ayah_start' => 1,
                        'ayah_end' => 30,
                        'grade' => 'A',
                    ],
                ],
            ],
        ]);

        $syncResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_submitted', 2)
            ->assertJsonPath('data.processed_count', 2);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'status' => 'H',
        ]);

        $this->assertDatabaseHas('tahfidz_logs', [
            'student_id' => $this->student->id,
            'surah_name' => 'Al-Mulk',
        ]);

        // 2. Idempotency test with same client_uuid
        $idempotentResponse = $this->postJson('/api/v1/debloat/sync/queue', [
            'actions' => [
                [
                    'client_uuid' => $clientUuid1,
                    'action_type' => 'ATTENDANCE_CHECKIN',
                    'payload' => ['status' => 'PRESENT'],
                ],
            ],
        ]);

        $idempotentResponse->assertStatus(200)
            ->assertJsonPath('data.results.0.status', 'PROCESSED')
            ->assertJsonPath('data.results.0.message', 'Action already processed previously (Idempotent).');

        // 3. Quick Pull test
        $pullResponse = $this->getJson('/api/v1/debloat/sync/pull');
        $pullResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Ahmad Kemal Siswa')
            ->assertJsonPath('data.user.nisn', '0069911223')
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'schedules',
                    'grades',
                    'announcements',
                    'server_time',
                ],
            ]);
    }

    /**
     * Test 85.5: Static Monolithic Module Catalog.
     */
    public function test_static_monolithic_module_catalog(): void
    {
        $response = $this->getJson('/api/v1/debloat/module-catalog');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'module_code',
                        'name',
                        'category',
                        'route_name',
                        'icon',
                    ],
                ],
            ]);
    }
}
