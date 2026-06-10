<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    Storage::fake('local');

    // 1. Setup Users
    $this->tu = User::factory()->create(['role' => 'staf_tu', 'name' => 'Sarah TU']);
    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Agus Admin']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Hadi Kepsek']);
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru']);

    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Ahmad Siswa']);
    $this->student2User = User::factory()->create(['role' => 'student', 'name' => 'Budi Siswa']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);

    // Create students
    $this->student1 = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '12345',
        'nisn' => '0012345678',
        'birth_date' => '2008-05-15',
    ]);

    $this->student2 = Student::factory()->create([
        'user_id' => $this->student2User->id,
        'classroom_id' => $this->classroom->id,
    ]);

    // Create employee for Budi Guru
    $this->employee = Employee::create([
        'user_id' => $this->guru->id,
        'employee_number' => 'PEG-112233',
        'position' => 'Guru Matematika',
        'employment_status' => 'gty',
        'hire_date' => '2020-01-01',
        'base_salary' => 4000000.00,
    ]);

    // 3. Create Letter Template
    $this->template = LetterTemplate::factory()->create([
        'name' => 'Surat Keterangan Aktif Siswa',
        'code' => 'SKA',
        'body_template' => 'Yang bertanda tangan di bawah ini menerangkan bahwa siswa bernama {{nama_siswa}} dengan NIS {{nis}} / NISN {{nisn}} kelas {{kelas}} lahir pada {{tanggal_lahir}} adalah benar aktif belajar. Surat ini dibuat untuk keperluan {{keperluan}}.',
        'required_fields' => ['keperluan'],
        'signer_name' => 'Drs. H. Soetarto, M.Pd.',
        'signer_title' => 'Kepala Sekolah',
        'is_active' => true,
    ]);
});

// ── ROLE-BASED AUTHORIZATION TESTS ───────────────────────────────────────────

test('staf tu and admin can view letters dashboard but student, parent and guru cannot', function () {
    // 1. As Staf TU (authorized)
    $response = $this->actingAs($this->tu)->get(route('letters.index'));
    $response->assertStatus(200);

    // 2. As Admin (authorized)
    $response = $this->actingAs($this->admin)->get(route('letters.index'));
    $response->assertStatus(200);

    // 3. As Kepala Sekolah (authorized)
    $response = $this->actingAs($this->kepsek)->get(route('letters.index'));
    $response->assertStatus(200);

    // 4. As Guru (unauthorized for viewAny)
    $response = $this->actingAs($this->guru)->get(route('letters.index'));
    $response->assertStatus(403);

    // 5. As Student (unauthorized)
    $response = $this->actingAs($this->studentUser)->get(route('letters.index'));
    $response->assertStatus(403);
});

test('staf tu can create a letter draft but others cannot', function () {
    // 1. As Staf TU (authorized)
    $response = $this->actingAs($this->tu)->post(route('letters.store'), [
        'template_id' => $this->template->id,
        'student_id' => $this->student1->id,
        'issue_date' => '2026-05-21',
        'field_values' => ['keperluan' => 'Mendaftar Beasiswa Luar Negeri'],
        'notes' => 'Catatan penting',
    ]);
    $response->assertRedirect(route('letters.index'));

    $this->assertDatabaseHas('letters', [
        'student_id' => $this->student1->id,
        'status' => 'draft',
    ]);

    // 2. As Guru (unauthorized)
    $response = $this->actingAs($this->guru)->post(route('letters.store'), [
        'template_id' => $this->template->id,
        'student_id' => $this->student1->id,
        'issue_date' => '2026-05-21',
        'field_values' => ['keperluan' => 'Mendaftar Beasiswa'],
    ]);
    $response->assertStatus(403);
});

// ── TEMPLATE MANAGEMENT TESTS ────────────────────────────────────────────────

test('staf tu and admin can manage templates but guru and students cannot', function () {
    // 1. Staf TU accesses manage page
    $response = $this->actingAs($this->tu)->get(route('letters.templates.index'));
    $response->assertStatus(200);

    // 2. Admin creates a template with signature upload
    $file = UploadedFile::fake()->image('signature.png', 200, 100);
    $response = $this->actingAs($this->admin)->post(route('letters.templates.store'), [
        'name' => 'Surat Tugas Guru',
        'code' => 'STG',
        'body_template' => 'Memberikan tugas kepada {{nama_pegawai}}.',
        'required_fields' => ['kegiatan'],
        'signer_name' => 'Drs. H. Soetarto, M.Pd.',
        'signer_title' => 'Kepala Sekolah',
        'signature' => $file,
    ]);
    $response->assertRedirect(route('letters.templates.index'));

    $newTemplate = LetterTemplate::where('code', 'STG')->first();
    expect($newTemplate)->not->toBeNull();
    expect($newTemplate->signature_path)->not->toBeNull();
    Storage::disk('local')->assertExists($newTemplate->signature_path);

    // 3. Guru cannot access manage templates page
    $response = $this->actingAs($this->guru)->get(route('letters.templates.index'));
    $response->assertStatus(403);
});

// ── PLACEHOLDER REPLACEMENTS & INTERACTIVE PREVIEWS ──────────────────────────

test('letter body replaces placeholders correctly with student and custom values', function () {
    $letter = Letter::factory()->create([
        'template_id' => $this->template->id,
        'student_id' => $this->student1->id,
        'created_by' => $this->tu->id,
        'issue_date' => '2026-05-21',
        'field_values' => ['keperluan' => 'Mendaftar Beasiswa LPDP'],
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->tu)->get(route('letters.show', $letter->id));
    $response->assertStatus(200);

    // Check placeholder replacements
    $response->assertSee('Ahmad Siswa');
    $response->assertSee('12345');
    $response->assertSee('0012345678');
    $response->assertSee('15-05-2008');
    $response->assertSee('Mendaftar Beasiswa LPDP');
});

// ── AUTO-NUMBERING & ISSUING WORKFLOWS ───────────────────────────────────────

test('issuing a draft letter generates correct sequential number and roman month', function () {
    // Letter 1 issued in May 2026
    $letter1 = Letter::factory()->create([
        'template_id' => $this->template->id,
        'student_id' => $this->student1->id,
        'created_by' => $this->tu->id,
        'issue_date' => '2026-05-10',
        'field_values' => ['keperluan' => 'Keperluan 1'],
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->tu)->post(route('letters.issue', $letter1->id));
    $response->assertRedirect(route('letters.show', $letter1->id));
    $response->assertSessionHas('success');

    $letter1->refresh();
    expect($letter1->status)->toBe('issued');
    // Sequence should be 001, code SKA, school SMA-ISA, month V (May = 5 = V), year 2026
    expect($letter1->letter_number)->toBe('001/SKA/SMA-ISA/V/2026');

    // Letter 2 issued in May 2026 (same template)
    $letter2 = Letter::factory()->create([
        'template_id' => $this->template->id,
        'student_id' => $this->student2->id,
        'created_by' => $this->tu->id,
        'issue_date' => '2026-05-15',
        'field_values' => ['keperluan' => 'Keperluan 2'],
        'status' => 'draft',
    ]);

    $this->actingAs($this->tu)->post(route('letters.issue', $letter2->id));
    $letter2->refresh();
    // Sequence incremented to 002
    expect($letter2->letter_number)->toBe('002/SKA/SMA-ISA/V/2026');
});

// ── BULK GENERATION FOR CLASSROOMS ───────────────────────────────────────────

test('staf tu can bulk generate draft letters for all students in a classroom', function () {
    $response = $this->actingAs($this->tu)->get(route('letters.bulk'));
    $response->assertStatus(200);

    $responseStore = $this->actingAs($this->tu)->post(route('letters.bulk.store'), [
        'template_id' => $this->template->id,
        'classroom_id' => $this->classroom->id,
        'field_values' => ['keperluan' => 'Persiapan Ujian Akhir'],
    ]);

    $responseStore->assertRedirect(route('letters.index'));
    $responseStore->assertSessionHas('success');

    // Verify draft letters were created for both students in the classroom
    $this->assertDatabaseHas('letters', [
        'template_id' => $this->template->id,
        'student_id' => $this->student1->id,
        'status' => 'draft',
    ]);

    $this->assertDatabaseHas('letters', [
        'template_id' => $this->template->id,
        'student_id' => $this->student2->id,
        'status' => 'draft',
    ]);
});

// ── PRINT & PDF EXPORT GENERATION ────────────────────────────────────────────

test('authorized users can download letter pdf with secure digital signature image', function () {
    // Create template signature file in storage
    $signatureFile = 'signatures/tst_sig.png';
    Storage::disk('local')->put($signatureFile, 'fake_png_data');
    $this->template->update(['signature_path' => $signatureFile]);

    $letter = Letter::factory()->create([
        'template_id' => $this->template->id,
        'student_id' => $this->student1->id,
        'created_by' => $this->tu->id,
        'issue_date' => '2026-05-21',
        'field_values' => ['keperluan' => 'Mendaftar Beasiswa LPDP'],
        'status' => 'issued',
        'letter_number' => '001/SKA/SMA-ISA/V/2026',
    ]);

    // Download PDF
    $response = $this->actingAs($this->tu)->get(route('letters.pdf', $letter->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('content-disposition', 'attachment; filename=001_SKA_SMA-ISA_V_2026.pdf');
});
