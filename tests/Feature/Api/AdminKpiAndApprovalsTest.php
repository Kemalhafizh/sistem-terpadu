<?php

namespace Tests\Feature\Api;

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * FASE 71.4 follow-up: real KPI computation (SPP ratio per academic year,
 * CBT server usage) and a real GET endpoint for the pending-approvals list
 * — both previously either "Dummy" (KPI) or entirely missing (approvals list).
 */
class AdminKpiAndApprovalsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    public function test_pending_approvals_mengembalikan_daftar_nyata_bukan_hanya_jumlah()
    {
        Sanctum::actingAs($this->adminUser);

        $requester = User::factory()->create(['role' => 'guru']);
        $definition = WorkflowDefinition::create([
            'name' => 'Pengajuan Cuti',
            'code' => 'LEAVE-001',
            'is_active' => true,
            'steps' => [['order' => 1, 'role' => 'kepsek'], ['order' => 2, 'role' => 'admin']],
        ]);

        $pending = WorkflowRequest::create([
            'definition_id' => $definition->id,
            'requester_id' => $requester->id,
            'requestable_type' => 'App\\Models\\User',
            'requestable_id' => $requester->id,
            'status' => 'pending',
            'notes' => 'Cuti sakit 3 hari',
            'current_step' => 1,
            'due_at' => now()->addDays(2),
        ]);

        // An already-approved request must NOT show up in the pending list.
        WorkflowRequest::create([
            'definition_id' => $definition->id,
            'requester_id' => $requester->id,
            'requestable_type' => 'App\\Models\\User',
            'requestable_id' => $requester->id,
            'status' => 'approved',
            'current_step' => 2,
        ]);

        $response = $this->getJson('/api/v1/mobile/admin/approvals');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $pending->id,
            'type_name' => 'Pengajuan Cuti',
            'requester_name' => $requester->name,
            'notes' => 'Cuti sakit 3 hari',
            'current_step' => 1,
            'total_steps' => 2,
        ]);
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
