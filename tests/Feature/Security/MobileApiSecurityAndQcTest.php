<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\Classroom;
use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\LibraryBook;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\StartsWorkflows;
use Tests\TestCase;

class MobileApiSecurityAndQcTest extends TestCase
{
    use RefreshDatabase, StartsWorkflows;

    protected User $adminUser;

    protected User $teacherUser;

    protected User $parentUserA;

    protected User $parentUserB;

    protected User $studentUserA;

    protected User $studentUserB;

    protected Student $studentA;

    protected Student $studentB;

    protected Classroom $classroom;

    protected AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin', 'name' => 'Admin Sekolah']);
        $this->teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru']);
        $this->parentUserA = User::factory()->create(['role' => 'parent', 'name' => 'Orang Tua A']);
        $this->parentUserB = User::factory()->create(['role' => 'parent', 'name' => 'Orang Tua B']);
        $this->studentUserA = User::factory()->create(['role' => 'student', 'name' => 'Siswa A']);
        $this->studentUserB = User::factory()->create(['role' => 'student', 'name' => 'Siswa B']);

        $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $this->classroom = Classroom::factory()->create(['academic_year' => '2026/2027']);

        $this->studentA = Student::factory()->create([
            'user_id' => $this->studentUserA->id,
            'parent_id' => $this->parentUserA->id,
            'classroom_id' => $this->classroom->id,
            'nis' => '10001',
        ]);

        $this->studentB = Student::factory()->create([
            'user_id' => $this->studentUserB->id,
            'parent_id' => $this->parentUserB->id,
            'classroom_id' => $this->classroom->id,
            'nis' => '10002',
        ]);
    }

    /**
     * VULN-031 Test: Mobile admin dashboard, KPI, and approvals enforce strict role authorization.
     */
    public function test_non_admin_users_are_forbidden_from_mobile_admin_dashboard_and_approvals(): void
    {
        // 1. Student attempting to view admin dashboard -> 403
        Sanctum::actingAs($this->studentUserA);
        $this->getJson('/api/v1/mobile/admin/dashboard')->assertStatus(403);
        $this->getJson('/api/v1/mobile/admin/kpi')->assertStatus(403);

        // 2. Student attempting to approve workflow -> 403
        $req = $this->startLeaveWorkflow($this->teacherUser, ['admin'], 'Pengadaan Barang', 'Permohonan pengadaan');

        $this->postJson("/api/v1/mobile/admin/approvals/{$req->id}/action", [
            'action' => 'approve',
            'notes' => 'Bypass attempt by student',
        ])->assertStatus(403);

        // 3. Admin can access successfully -> 200
        Sanctum::actingAs($this->adminUser);
        $this->getJson('/api/v1/mobile/admin/dashboard')->assertStatus(200);
        $this->getJson('/api/v1/mobile/admin/kpi')->assertStatus(200);
        $this->postJson("/api/v1/mobile/admin/approvals/{$req->id}/action", [
            'action' => 'approve',
            'notes' => 'Approved by administrator',
        ])->assertStatus(200);
    }

    /**
     * VULN-032 Test: Parent portal prevents cross-parent BOLA / IDOR.
     */
    public function test_parent_portal_prevents_cross_child_inspection(): void
    {
        Sanctum::actingAs($this->parentUserA);

        // Parent A can access their own child A
        $ownRes = $this->getJson("/api/v1/parent/child/{$this->studentA->id}/feed");
        $ownRes->assertStatus(200);

        // Parent A attempts to inspect Child B -> MUST BE 403
        $attackRes = $this->getJson("/api/v1/parent/child/{$this->studentB->id}/feed");
        $attackRes->assertStatus(403);

        $attackDigest = $this->getJson("/api/v1/parent/child/{$this->studentB->id}/digest");
        $attackDigest->assertStatus(403);

        $attackBench = $this->getJson("/api/v1/parent/child/{$this->studentB->id}/benchmark");
        $attackBench->assertStatus(403);

        // Student A cannot access Student B's parent feed
        Sanctum::actingAs($this->studentUserA);
        $this->getJson("/api/v1/parent/child/{$this->studentB->id}/feed")->assertStatus(403);
    }

    /**
     * VULN-033 Test: Visualization student radar and trend prevent unauthorized access.
     */
    public function test_visualization_student_radar_and_trend_prevent_cross_student_inspection(): void
    {
        Sanctum::actingAs($this->studentUserA);

        // Student A inspecting own radar & trend -> 200
        $this->getJson("/api/v1/viz/radar/student/{$this->studentA->id}")->assertStatus(200);
        $this->getJson("/api/v1/viz/trend/student/{$this->studentA->id}")->assertStatus(200);

        // Student A inspecting Student B -> 403
        $this->getJson("/api/v1/viz/radar/student/{$this->studentB->id}")->assertStatus(403);
        $this->getJson("/api/v1/viz/trend/student/{$this->studentB->id}")->assertStatus(403);

        // Teacher CAN inspect Student B's pedagogical radar -> 200
        Sanctum::actingAs($this->teacherUser);
        $this->getJson("/api/v1/viz/radar/student/{$this->studentB->id}")->assertStatus(200);
    }

    /**
     * VULN-034 Test: Library self-checkout prevents student IDOR spoofing.
     */
    public function test_library_self_checkout_prevents_student_impersonation(): void
    {
        $book = LibraryBook::factory()->create([
            'barcode' => 'BOOK-SCAN-001',
            'type' => 'physical',
            'is_physical_available' => true,
            'total_copies' => 5,
            'available_copies' => 5,
        ]);

        Sanctum::actingAs($this->studentUserA);

        // Student A tries to borrow under Student B's id -> 403 Forbidden
        $response = $this->postJson('/api/v1/library/self-checkout', [
            'book_qr_code' => 'BOOK-SCAN-001',
            'student_id' => $this->studentB->id,
        ]);

        $response->assertStatus(403);
        $this->assertStringContainsString('dilarang melakukan peminjaman atas nama identitas siswa lain', $response->json('message'));
    }

    /**
     * VULN-035 Test: Achievement verification strictly blocks non-authorized roles (e.g. parent, student).
     */
    public function test_achievement_verification_enforces_strict_role_whitelist(): void
    {
        $achievement = Achievement::create([
            'student_id' => $this->studentA->id,
            'title' => 'Juara 1 Debat Bahasa Inggris',
            'field' => 'Bahasa',
            'level' => 'PROVINSI',
            'organizer' => 'Dinas Pendidikan',
            'event_date' => now()->toDateString(),
            'verification_status' => 'PENDING_REVIEW',
            'points_earned' => 25,
        ]);

        // Parent role cannot approve achievement -> 403
        Sanctum::actingAs($this->parentUserA);
        $this->postJson("/api/v1/achievements/{$achievement->id}/verify", [
            'approve' => true,
        ])->assertStatus(403);

        // Student role cannot approve achievement -> 403
        Sanctum::actingAs($this->studentUserA);
        $this->postJson("/api/v1/achievements/{$achievement->id}/verify", [
            'approve' => true,
        ])->assertStatus(403);

        // Guru role CAN approve achievement -> 200
        Sanctum::actingAs($this->teacherUser);
        $res = $this->postJson("/api/v1/achievements/{$achievement->id}/verify", [
            'approve' => true,
        ]);
        $res->assertStatus(200);
    }

    /**
     * VULN-036 Test: Web push subscribe rejects unauthenticated requests.
     */
    public function test_web_push_subscribe_requires_authentication(): void
    {
        $response = $this->postJson('/pwa/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/rogue-token',
        ]);

        $response->assertStatus(401);
    }

    /**
     * VULN-038 Test: Teacher evaluation prevents duplicate submissions even if anonymous.
     */
    public function test_anonymous_teacher_evaluation_prevents_duplicate_spam(): void
    {
        $cycle = Evaluation360Cycle::create([
            'title' => 'Evaluasi Kinerja Guru',
            'academic_year' => '2026/2027',
            'semester' => 'Ganjil',
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
            'created_by' => $this->adminUser->id,
        ]);

        Evaluation360Instrument::create([
            'cycle_id' => $cycle->id,
            'evaluator_type' => 'student',
            'questions' => ['Bagaimana keteladanan guru?'],
            'scale_type' => 'likert5',
            'is_anonymous' => true,
        ]);

        // Budi teaches the student's class this year: only such teachers can be rated.
        Schedule::factory()->create(['academic_year_id' => $this->academicYear->id, 'classroom_id' => $this->classroom->id, 'teacher_id' => $this->teacherUser->id]);

        Sanctum::actingAs($this->studentUserA);

        // First review -> 200
        $first = $this->postJson('/api/v1/evaluations/teachers/submit', [
            'teacher_id' => $this->teacherUser->id,
            'rating' => 5,
            'comments' => 'Guru sangat inspiratif',
        ]);
        $first->assertStatus(200);

        // Second review attempt -> 400 Bad Request
        $second = $this->postJson('/api/v1/evaluations/teachers/submit', [
            'teacher_id' => $this->teacherUser->id,
            'rating' => 1,
            'comments' => 'Spam rating attempt',
        ]);
        $second->assertStatus(400);
        $this->assertStringContainsString('Anda sudah mengevaluasi guru ini', $second->json('message'));
    }

    /**
     * VULN-039 Test: UTBK submit gracefully rejects users without student profile.
     */
    public function test_utbk_submit_handles_null_student_cleanly(): void
    {
        // Non-students never reach the endpoint at all…
        Sanctum::actingAs(User::factory()->create(['role' => 'guru']));
        $this->postJson('/api/v1/utbk/tryouts/1/submit', ['answers' => [1 => 'A']])->assertForbidden();

        // …and a student account without its student record gets a clear 422.
        $userWithoutStudent = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($userWithoutStudent);

        $response = $this->postJson('/api/v1/utbk/tryouts/1/submit', [
            'answers' => [1 => 'A'],
        ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('Profil siswa tidak ditemukan', $response->json('message'));
    }
}
