<?php

namespace Tests\Feature\Security;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Security\SsrfGuardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ZeroTrustZeroDayRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $studentUserA;

    protected User $studentUserB;

    protected Student $studentA;

    protected Student $studentB;

    protected Classroom $classA;

    protected Classroom $classB;

    protected Exam $examClassA;

    protected QuestionBank $question1;

    protected QuestionBank $foreignQuestion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.zt@sa1.sch.id',
        ]);

        $this->classA = Classroom::factory()->create(['name' => 'XII MIPA 1']);
        $this->classB = Classroom::factory()->create(['name' => 'XII MIPA 2']);

        $this->studentUserA = User::factory()->create(['role' => 'student', 'email' => 'student.a@sa1.sch.id']);
        $this->studentA = Student::factory()->create([
            'user_id' => $this->studentUserA->id,
            'classroom_id' => $this->classA->id,
        ]);

        $this->studentUserB = User::factory()->create(['role' => 'student', 'email' => 'student.b@sa1.sch.id']);
        $this->studentB = Student::factory()->create([
            'user_id' => $this->studentUserB->id,
            'classroom_id' => $this->classB->id,
        ]);

        $subject = Subject::factory()->create();
        $academicYear = AcademicYear::factory()->create(['is_active' => true]);

        $this->examClassA = Exam::factory()->create([
            'classroom_id' => $this->classA->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'created_by' => $this->adminUser->id,
            'status' => 'published',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(2),
        ]);

        $this->question1 = QuestionBank::create([
            'subject_id' => $subject->id,
            'created_by' => $this->adminUser->id,
            'question_text' => 'Berapakah 1 + 1?',
            'type' => 'essay',
            'difficulty' => 'easy',
            'grade_level' => 'XII',
        ]);
        $this->examClassA->questions()->attach($this->question1->id, ['order' => 1, 'weight' => 10]);

        $this->foreignQuestion = QuestionBank::create([
            'subject_id' => $subject->id,
            'created_by' => $this->adminUser->id,
            'question_text' => 'Soal dari paket ujian lain',
            'type' => 'essay',
            'difficulty' => 'easy',
            'grade_level' => 'XII',
        ]);
    }

    /**
     * VULN-044 Test: CBT microSync & essay upload enforce take authorization policy.
     */
    public function test_cbt_micro_sync_and_essay_upload_enforce_take_policy(): void
    {
        // Student B (Class B) attempts microSync to Exam for Class A -> 403 Forbidden
        Sanctum::actingAs($this->studentUserB);
        $resSync = $this->postJson("/api/v1/student/cbt/exams/{$this->examClassA->id}/micro-sync", [
            'answers' => [
                (string) $this->question1->id => 'A',
            ],
        ]);
        $resSync->assertStatus(403);

        // Student B attempts essay upload to Exam for Class A -> 403 Forbidden
        $file = UploadedFile::fake()->image('essay.jpg', 600, 800);
        $resEssay = $this->postJson("/api/v1/student/cbt/exams/{$this->examClassA->id}/upload-essay", [
            'question_id' => $this->question1->id,
            'file' => $file,
        ]);
        $resEssay->assertStatus(403);

        // Student A (enrolled in Class A) can sync answers
        Sanctum::actingAs($this->studentUserA);
        $resOk = $this->postJson("/api/v1/student/cbt/exams/{$this->examClassA->id}/micro-sync", [
            'answers' => [
                (string) $this->question1->id => 'A',
            ],
        ]);
        $resOk->assertStatus(200);
    }

    /**
     * VULN-044 Test: CBT attempt state machine prevents state reversion after completion or proctor lock.
     */
    public function test_cbt_attempt_state_machine_blocks_reversion(): void
    {
        Sanctum::actingAs($this->studentUserA);

        // Create an attempt locked by proctor for cheating
        $attempt = ExamAttempt::firstOrCreate(
            ['exam_id' => $this->examClassA->id, 'student_id' => $this->studentA->id],
            [
                'status' => 'blocked_violation',
                'started_at' => now()->subMinutes(30),
            ]
        );
        $attempt->update(['status' => 'blocked_violation']);

        // Student A sends microSync -> must be rejected 403, cannot revert to in_progress
        $resSync = $this->postJson("/api/v1/student/cbt/exams/{$this->examClassA->id}/micro-sync", [
            'answers' => [(string) $this->question1->id => 'B'],
        ]);
        $resSync->assertStatus(403);

        $attempt->refresh();
        $this->assertEquals('blocked_violation', $attempt->status);

        // Essay upload must also be rejected
        $file = UploadedFile::fake()->image('essay.jpg', 600, 800);
        $resEssay = $this->postJson("/api/v1/student/cbt/exams/{$this->examClassA->id}/upload-essay", [
            'question_id' => $this->question1->id,
            'file' => $file,
        ]);
        $resEssay->assertStatus(403);
    }

    /**
     * VULN-044 Test: CBT essay upload validates question belongs to exam.
     */
    public function test_cbt_essay_upload_validates_question_belongs_to_exam(): void
    {
        Sanctum::actingAs($this->studentUserA);

        $file = UploadedFile::fake()->image('essay.jpg', 600, 800);
        $res = $this->postJson("/api/v1/student/cbt/exams/{$this->examClassA->id}/upload-essay", [
            'question_id' => $this->foreignQuestion->id,
            'file' => $file,
        ]);

        $res->assertStatus(422);
    }

    /**
     * VULN-045 Test: SSRF guard blocks cloud metadata endpoints and invalid protocols.
     */
    public function test_ssrf_guard_blocks_cloud_metadata_and_invalid_protocols(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SsrfGuardService::validateUrl('http://169.254.169.254/latest/meta-data/');
    }

    /**
     * VULN-045 Test: SSRF guard blocks non-http schemes.
     */
    public function test_ssrf_guard_blocks_non_http_schemes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SsrfGuardService::validateUrl('file:///etc/passwd');
    }

    /**
     * VULN-046 Test: Midtrans webhook rejects non-positive gross amounts and verifies signatures.
     */
    public function test_midtrans_webhook_requires_positive_gross_amount_and_valid_signature(): void
    {
        config(['services.midtrans.server_key' => 'test_server_key_sa1_2026']);

        // Zero or negative gross amount -> 400
        $resZero = $this->postJson('/api/midtrans/webhook', [
            'order_id' => 'BILL-1-12345',
            'status_code' => '200',
            'gross_amount' => '0',
            'signature_key' => 'invalid',
        ]);
        $resZero->assertStatus(400);

        // Invalid signature -> 403
        $resBadSig = $this->postJson('/api/midtrans/webhook', [
            'order_id' => 'BILL-1-12345',
            'status_code' => '200',
            'gross_amount' => '500000',
            'signature_key' => 'bad_signature_key_hash',
        ]);
        $resBadSig->assertStatus(403);
    }

    /**
     * VULN-047 Test: Offline sync engine validates batch count and binds user context.
     */
    public function test_offline_sync_validates_batch_count_and_binds_user(): void
    {
        Sanctum::actingAs($this->studentUserA);

        // Valid payload
        $res = $this->postJson('/api/v1/sync/batch', [
            'actions' => [
                [
                    'id' => 'sync-item-1',
                    'type' => 'mutabaah_draft',
                    'data' => ['prayer' => 'subuh'],
                ],
            ],
        ]);

        $res->assertStatus(200);
        $this->assertEquals(1, $res->json('data.processed'));

        // Oversized payload (> 100 items) -> 422 Unprocessable Entity
        $largeActions = [];
        for ($i = 0; $i < 101; $i++) {
            $largeActions[] = ['id' => "item-$i", 'type' => 'tahsin', 'data' => []];
        }

        $resOversized = $this->postJson('/api/v1/sync/batch', [
            'actions' => $largeActions,
        ]);
        $resOversized->assertStatus(422);
    }

    /**
     * VULN-048 Test: Impersonation regenerates session and restricts sensitive administrative mutations.
     */
    public function test_impersonation_regenerates_session_and_restricts_sensitive_routes(): void
    {
        // Admin impersonating Student A
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.impersonate.take', $this->studentUserA));
        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($this->studentUserA->id, auth()->id());
        $this->assertEquals($this->adminUser->id, session('impersonate_by'));

        // While impersonating, destructive routes are forbidden (403)
        $this->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertStatus(403);

        // Return from impersonation
        $leaveResponse = $this->get(route('admin.impersonate.leave'));
        $leaveResponse->assertRedirect(route('users.index'));
        $this->assertEquals($this->adminUser->id, auth()->id());
        $this->assertFalse(session()->has('impersonate_by'));
    }
}
