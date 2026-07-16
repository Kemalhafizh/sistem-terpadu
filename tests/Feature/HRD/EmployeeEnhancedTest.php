<?php

/** @var TestCase|stdClass $this */

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeTraining;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    Storage::fake('public');

    // Setup Users
    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Agus Admin']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Hadi Kepsek']);
    $this->guruUser = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    // Setup Employee
    $this->employee = Employee::create([
        'user_id' => $this->guruUser->id,
        'employee_number' => 'PEG-112233',
        'position' => 'Guru Matematika',
        'employment_status' => 'gty',
        'hire_date' => '2025-01-01',
    ]);
});

test('admin or kepsek can access hrd dashboards and org chart', function () {
    $this->actingAs($this->admin);

    $this->get(route('hrd.dashboard'))->assertStatus(200);
    $this->get(route('hrd.org-chart'))->assertStatus(200);
    $this->get(route('hrd.attendance-report'))->assertStatus(200);
    $this->get(route('hrd.certification-tracker'))->assertStatus(200);
});

test('unauthorized roles are blocked from hrd administration', function () {
    $this->actingAs($this->parentUser);

    $this->get(route('hrd.dashboard'))->assertStatus(403);
    $this->get(route('hrd.org-chart'))->assertStatus(403);
});

test('admin can upload and delete employee documents', function () {
    $this->actingAs($this->admin);

    $file = UploadedFile::fake()->create('sk_pengangkatan.pdf', 500);

    $response = $this->post(route('hrd.employees.documents.store', $this->employee->id), [
        'title' => 'SK Pengangkatan Yayasan 2026',
        'type' => 'sk',
        'file' => $file,
        'issue_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'notes' => 'Catatan dokumen SK.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('employee_documents', [
        'employee_id' => $this->employee->id,
        'title' => 'SK Pengangkatan Yayasan 2026',
        'type' => 'sk',
    ]);

    $document = EmployeeDocument::where('employee_id', $this->employee->id)->first();

    $deleteResponse = $this->delete(route('hrd.employees.documents.destroy', [$this->employee->id, $document->id]));
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('employee_documents', [
        'id' => $document->id,
    ]);
});

test('admin can add employee training log', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('hrd.employees.trainings.store', $this->employee->id), [
        'title' => 'Workshop IT Kurikulum Merdeka',
        'type' => 'workshop',
        'organizer' => 'Kemendikbud',
        'start_date' => '2026-02-10',
        'end_date' => '2026-02-12',
        'hours' => 32,
        'certificate_number' => 'CERT-998877',
        'certificate_expiry' => '2029-02-12',
        'notes' => 'Catatan pelatihan.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('employee_trainings', [
        'employee_id' => $this->employee->id,
        'title' => 'Workshop IT Kurikulum Merdeka',
        'hours' => 32,
    ]);
});

test('admin can store performance evaluations with auto computed total score and grade', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('hrd.employees.evaluation.store', $this->employee->id), [
        'period' => 'Semester Genap 2025/2026',
        'pedagogik' => 90,
        'profesional' => 85,
        'sosial' => 80,
        'kepribadian' => 85,
        'strengths' => 'Sangat disiplin dan berintegritas.',
        'improvements' => 'Tingkatkan penggunaan IT.',
        'goals' => 'Sertifikasi IT.',
    ]);

    $response->assertRedirect(route('hrd.evaluations.report'));
    
    // Average: (90+85+80+85)/4 = 85.0 -> Grade A
    $this->assertDatabaseHas('performance_evaluations', [
        'employee_id' => $this->employee->id,
        'period' => 'Semester Genap 2025/2026',
        'total_score' => 85.00,
        'grade' => 'A',
    ]);
});

test('admin can export reports and cards', function () {
    $this->actingAs($this->admin);

    // 1. Export CSV Attendance
    $this->get(route('hrd.attendance-report.export'))->assertStatus(200);

    // 2. Export Employee Card PDF
    $this->get(route('hrd.employees.id-card', $this->employee->id))->assertStatus(200);
});
