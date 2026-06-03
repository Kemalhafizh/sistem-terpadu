<?php

use App\Models\AcademicYear;
use App\Models\OsisTransaction;
use App\Models\User;
use App\Models\MpkLpj;
use App\Models\Student;
use App\Models\StudentAspiration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Clean storage
    Storage::fake('public');

    // Create Academic Year
    $this->academicYear = AcademicYear::create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-01',
        'end_date' => '2027-06-30',
    ]);

    // Create School Admin
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    // Create Waka Kesiswaan
    $this->wakaKesiswaan = User::factory()->create([
        'role' => 'waka_kesiswaan',
    ]);

    // Create Teacher (Guru)
    $this->guru = User::factory()->create([
        'role' => 'guru',
    ]);

    // Create Student User
    $this->studentUser = User::factory()->create([
        'role' => 'student',
    ]);
    
    // Create Student Profile
    $this->student = Student::create([
        'user_id' => $this->studentUser->id,
        'nis' => '123456',
        'nisn' => '0012345678',
        'gender' => 'L',
        'birth_date' => '2010-05-15',
        'address' => 'Semarang',
        'status' => 'active',
    ]);

    // Create an OSIS transaction
    $this->osisTx = OsisTransaction::create([
        'type' => 'expense',
        'amount' => 500000.00,
        'description' => 'Pembelian Banner Class Meeting',
        'category' => 'Kegiatan',
        'transaction_date' => '2026-05-23',
        'recorded_by' => $this->admin->id,
    ]);
});

test('unauthorized users cannot view mpk dashboard', function () {
    $this->actingAs($this->studentUser)
        ->get(route('mpk.index'))
        ->assertStatus(403);
});

test('authorized school managers can view mpk dashboard', function () {
    $this->actingAs($this->wakaKesiswaan)
        ->get(route('mpk.index'))
        ->assertStatus(200)
        ->assertSee('Dashboard Pengawasan')
        ->assertSee('Pembelian Banner Class Meeting');
});

test('mpk auditor can audit osis transactions', function () {
    $this->actingAs($this->wakaKesiswaan)
        ->post(route('mpk.audit.store'), [
            'osis_transaction_id' => $this->osisTx->id,
            'status' => 'verified',
            'notes' => 'Sesuai dengan nota kuitansi fisik.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('mpk_audits', [
        'osis_transaction_id' => $this->osisTx->id,
        'status' => 'verified',
        'notes' => 'Sesuai dengan nota kuitansi fisik.',
        'audited_by' => $this->wakaKesiswaan->id,
    ]);
});

test('osis submitter can upload lpj document', function () {
    $file = UploadedFile::fake()->create('lpj_osis_2026.pdf', 1000, 'application/pdf');

    $this->actingAs($this->studentUser)
        ->post(route('mpk.lpj.store'), [
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Laporan Pertanggungjawaban Class Meeting',
            'description' => 'Evaluasi lengkap anggaran dan panitia',
            'file' => $file,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('mpk_lpjs', [
        'title' => 'Laporan Pertanggungjawaban Class Meeting',
        'status' => 'submitted',
        'submitted_by' => $this->studentUser->id,
    ]);
});

test('mpk reviewer can approve or reject lpj', function () {
    // Create an LPJ
    $lpj = MpkLpj::create([
        'academic_year_id' => $this->academicYear->id,
        'title' => 'LPJ Pentas Seni 2026',
        'description' => 'Evaluasi konser pentas seni sekolah',
        'file_path' => 'uploads/mpk/lpjs/dummy.pdf',
        'status' => 'submitted',
        'submitted_by' => $this->studentUser->id,
    ]);

    $this->actingAs($this->wakaKesiswaan)
        ->post(route('mpk.lpj.review', $lpj->uuid), [
            'status' => 'approved_with_notes',
            'reviewer_notes' => 'Laporan rapi, harap lampirkan sisa dana.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('mpk_lpjs', [
        'id' => $lpj->id,
        'status' => 'approved_with_notes',
        'reviewer_notes' => 'Laporan rapi, harap lampirkan sisa dana.',
        'reviewed_by' => $this->wakaKesiswaan->id,
    ]);
});

test('students can submit anonymous aspirations', function () {
    $this->actingAs($this->studentUser)
        ->post(route('mpk.aspirations.store'), [
            'title' => 'Kerusakan Pendingin Ruangan (AC) Lab Komputer',
            'body' => 'AC di Lab Komputer B sering mati dan bocor sehingga mengganggu konsentrasi KBM.',
            'category' => 'sarpras',
            'is_anonymous' => 1,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('student_aspirations', [
        'title' => 'Kerusakan Pendingin Ruangan (AC) Lab Komputer',
        'category' => 'sarpras',
        'is_anonymous' => true,
        'student_id' => $this->student->id,
        'status' => 'pending',
    ]);
});

test('mpk board can resolve student aspirations', function () {
    // Create an aspiration
    $aspiration = StudentAspiration::create([
        'title' => 'Menu Kantin Kurang Higienis',
        'body' => 'Beberapa wadah makanan terbuka bebas, mohon ditinjau ulang oleh sekolah.',
        'category' => 'umum',
        'is_anonymous' => false,
        'student_id' => $this->student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->wakaKesiswaan)
        ->post(route('mpk.aspirations.resolve', $aspiration->uuid), [
            'status' => 'resolved',
            'resolution_notes' => 'Sekolah telah melakukan inspeksi kantin hari ini dan mewajibkan semua wadah makanan tertutup.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('student_aspirations', [
        'id' => $aspiration->id,
        'status' => 'resolved',
        'resolution_notes' => 'Sekolah telah melakukan inspeksi kantin hari ini dan mewajibkan semua wadah makanan tertutup.',
        'resolved_by' => $this->wakaKesiswaan->id,
    ]);
});
