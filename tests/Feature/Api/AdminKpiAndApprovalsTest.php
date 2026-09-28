<?php

namespace Tests\Feature\Api;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\StartsWorkflows;
use Tests\TestCase;

/**
 * FASE 71.4 follow-up: real KPI computation (SPP ratio per academic year,
 * CBT server usage) and a real GET endpoint for the pending-approvals list
 * — both previously either "Dummy" (KPI) or entirely missing (approvals list).
 */
class AdminKpiAndApprovalsTest extends TestCase
{
    use RefreshDatabase, StartsWorkflows;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    public function test_pending_approvals_mengembalikan_daftar_nyata_bukan_hanya_jumlah()
    {
        $requester = User::factory()->create(['role' => 'guru']);
        $kepsek = User::factory()->create(['role' => 'kepsek']);
        $pending = $this->startLeaveWorkflow($requester, ['kepsek', 'admin'], 'Pengajuan Cuti', 'Cuti sakit 3 hari');

        // Step 1 belongs to the kepsek: the admin has nothing to decide yet.
        Sanctum::actingAs($this->adminUser);
        $this->getJson('/api/v1/mobile/admin/approvals')->assertOk()->assertJsonCount(0, 'data');

        Sanctum::actingAs($kepsek);
        $this->postJson("/api/v1/mobile/admin/approvals/{$pending->id}/action", ['action' => 'approve'])->assertOk();

        // A finished request never shows up.
        $done = $this->startLeaveWorkflow($requester, ['admin']);
        Sanctum::actingAs($this->adminUser);
        $this->postJson("/api/v1/mobile/admin/approvals/{$done->id}/action", ['action' => 'approve'])->assertOk();

        $response = $this->getJson('/api/v1/mobile/admin/approvals');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $pending->id,
            'type_name' => 'Pengajuan Cuti',
            'requester_name' => $requester->name,
            'notes' => 'Cuti sakit 3 hari',
            'current_step' => 2,
            'total_steps' => 2,
        ]);
    }

    public function test_persetujuan_mengikuti_langkah_dan_tidak_melompati_penyetuju()
    {
        $requester = User::factory()->create(['role' => 'guru']);
        $kepsek = User::factory()->create(['role' => 'kepsek']);
        $request = $this->startLeaveWorkflow($requester, ['kepsek', 'admin']);

        // The web's "waiting for you" list and the mobile list share this query;
        // it used to be empty for everyone.
        $workflow = app(WorkflowService::class);
        $this->assertSame([$request->id], $workflow->getPendingForUser($kepsek)->pluck('id')->all());
        $this->assertSame([], $workflow->getPendingForUser($this->adminUser)->pluck('id')->all());

        // The admin used to approve the whole request at step 1 in one tap.
        Sanctum::actingAs($this->adminUser);
        $this->postJson("/api/v1/mobile/admin/approvals/{$request->id}/action", ['action' => 'approve'])->assertForbidden();
        $this->assertSame('pending', $request->fresh()->status);

        // The kepsek approves step 1: the request moves on, it is not finished.
        Sanctum::actingAs($kepsek);
        $this->postJson("/api/v1/mobile/admin/approvals/{$request->id}/action", ['action' => 'approve'])
            ->assertOk()->assertJsonPath('message', 'Disetujui. Pengajuan diteruskan ke langkah berikutnya.');
        $this->assertSame('in_review', $request->fresh()->status);
        $this->assertSame('pending', $request->requestable->fresh()->status);

        // The admin decides the last step, and the leave request itself is approved.
        Sanctum::actingAs($this->adminUser);
        $this->postJson("/api/v1/mobile/admin/approvals/{$request->id}/action", ['action' => 'approve'])
            ->assertOk()->assertJsonPath('message', 'Pengajuan disetujui.');
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertSame('approved', $request->requestable->fresh()->status);
    }

    public function test_dashboard_menghitung_kehadiran_hari_ini_dari_status_nyata()
    {
        // Statuses are H/S/I/A; the old query counted "present"/"late" and read 0% every day.
        $year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $recorder = User::factory()->create(['role' => 'guru']);
        foreach (['H', 'H', 'H', 'S'] as $status) {
            $student = Student::factory()->create();
            Attendance::create([
                'student_id' => $student->id, 'academic_year_id' => $year->id, 'classroom_id' => $student->classroom_id,
                'date' => now()->toDateString(), 'status' => $status, 'recorded_by' => $recorder->id,
            ]);
        }

        Sanctum::actingAs($this->adminUser);
        $this->getJson('/api/v1/mobile/admin/dashboard')->assertOk()
            ->assertJsonPath('data.attendance_rate', 75)
            ->assertJsonPath('data.total_students', 4);
    }

    public function test_dashboard_tanpa_presensi_hari_ini_tidak_mengarang_persentase()
    {
        Sanctum::actingAs($this->adminUser);
        $this->getJson('/api/v1/mobile/admin/dashboard')->assertOk()->assertJsonPath('data.attendance_rate', null);
    }

    public function test_kpi_detail_menghitung_rasio_spp_per_tahun_ajaran_dari_data_nyata()
    {
        Sanctum::actingAs($this->adminUser);

        $year2025 = AcademicYear::create(['year' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => false]);
        $year2026 = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $student = Student::factory()->create();
        // One shared category — PaymentCategoryFactory only has 3 unique
        // names to draw from, which Billing::factory()'s implicit
        // relationship would otherwise exhaust after a few calls.
        $category = PaymentCategory::create(['name' => 'SPP Bulanan', 'default_amount' => 500000, 'is_monthly' => true]);

        // 2025/2026: 2 paid, 2 unpaid -> 50%
        Billing::factory()->count(2)->create(['student_id' => $student->id, 'payment_category_id' => $category->id, 'academic_year_id' => $year2025->id, 'status' => 'paid']);
        Billing::factory()->count(2)->create(['student_id' => $student->id, 'payment_category_id' => $category->id, 'academic_year_id' => $year2025->id, 'status' => 'unpaid']);

        // 2026/2027: 1 paid, 3 unpaid -> 25%
        Billing::factory()->count(1)->create(['student_id' => $student->id, 'payment_category_id' => $category->id, 'academic_year_id' => $year2026->id, 'status' => 'paid']);
        Billing::factory()->count(3)->create(['student_id' => $student->id, 'payment_category_id' => $category->id, 'academic_year_id' => $year2026->id, 'status' => 'unpaid']);

        $response = $this->getJson('/api/v1/mobile/admin/kpi');

        $response->assertStatus(200);
        $ratios = collect($response->json('data.spp_payment_ratio_by_cohort'));

        $ratio2025 = $ratios->firstWhere('academic_year', '2025/2026');
        $this->assertNotNull($ratio2025, 'Ratio untuk 2025/2026 harus ada di response');
        $this->assertEquals(4, $ratio2025['total_billings']);
        $this->assertEquals(2, $ratio2025['paid_billings']);
        $this->assertEquals(50.0, $ratio2025['paid_ratio_percent']);

        $ratio2026 = $ratios->firstWhere('academic_year', '2026/2027');
        $this->assertNotNull($ratio2026, 'Ratio untuk 2026/2027 harus ada di response');
        $this->assertEquals(4, $ratio2026['total_billings']);
        $this->assertEquals(1, $ratio2026['paid_billings']);
        $this->assertEquals(25.0, $ratio2026['paid_ratio_percent']);
    }

    public function test_kpi_detail_menghitung_penggunaan_server_cbt_dari_exam_attempt_nyata()
    {
        Sanctum::actingAs($this->adminUser);

        $subject = Subject::create(['name' => 'Matematika', 'code' => 'MTK', 'category' => 'Nasional']);
        $academicYear = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $exam = Exam::create([
            'title' => 'UTS Matematika',
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'created_by' => $this->adminUser->id,
            'type' => 'uts',
            'status' => 'published',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 90,
            'total_questions' => 20,
        ]);
        $students = Student::factory()->count(3)->create();

        ExamAttempt::create(['exam_id' => $exam->id, 'student_id' => $students[0]->id, 'status' => 'in_progress', 'started_at' => now()]);
        ExamAttempt::create(['exam_id' => $exam->id, 'student_id' => $students[1]->id, 'status' => 'in_progress', 'started_at' => now()]);
        ExamAttempt::create(['exam_id' => $exam->id, 'student_id' => $students[2]->id, 'status' => 'submitted', 'started_at' => now()->subHour(), 'submitted_at' => now()]);

        $response = $this->getJson('/api/v1/mobile/admin/kpi');

        $response->assertStatus(200);
        $response->assertJsonPath('data.cbt_server_usage.active_sessions', 2);
        $response->assertJsonPath('data.cbt_server_usage.sessions_today', 3);
    }

    /**
     * FASE 76.2 finding: monthly_revenue used to query `payment_date` on the
     * `billings` table — a column that only exists on `payments`. SQLite
     * silently treats an unknown double-quoted identifier as a string
     * literal (so this returned 0 locally and in every test), but MySQL
     * throws "Unknown column", which would 500 the whole KPI endpoint in
     * production. It also never scoped by year, so the same month of every
     * previous year was summed in.
     */
    public function test_kpi_monthly_revenue_dari_tabel_payments_bulan_dan_tahun_berjalan()
    {
        Sanctum::actingAs($this->adminUser);

        $year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $student = Student::factory()->create();
        $category = PaymentCategory::create(['name' => 'SPP Bulanan', 'default_amount' => 500000, 'is_monthly' => true]);
        $billing = Billing::factory()->create([
            'student_id' => $student->id,
            'payment_category_id' => $category->id,
            'academic_year_id' => $year->id,
            'status' => 'paid',
        ]);

        $pay = fn (int $amount, $date) => Payment::factory()->create([
            'billing_id' => $billing->id,
            'created_by' => $this->adminUser->id,
            'amount_paid' => $amount,
            'payment_date' => $date->toDateString(),
        ]);

        $pay(300000, now()->startOfMonth());
        $pay(200000, now()->startOfMonth());
        $pay(999000, now()->startOfMonth()->subYear());          // same month, last year — must be excluded
        $pay(777000, now()->startOfMonth()->subMonthNoOverflow()); // previous month — must be excluded

        $response = $this->getJson('/api/v1/mobile/admin/kpi');

        $response->assertStatus(200);
        $this->assertEquals(500000.0, $response->json('data.monthly_revenue'));
    }

    public function test_kpi_detail_tidak_mengarang_persentase_kehadiran_guru()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/v1/mobile/admin/kpi');

        $response->assertStatus(200);
        $response->assertJsonPath('data.teacher_attendance_rate', null);
        $this->assertNotEmpty($response->json('data.teacher_attendance_rate_note'));
    }

    public function test_role_selain_admin_kepsek_tidak_bisa_akses_approvals_dan_kpi()
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($studentUser);

        $this->getJson('/api/v1/mobile/admin/approvals')->assertStatus(403);
        $this->getJson('/api/v1/mobile/admin/kpi')->assertStatus(403);
    }
}
