<?php

use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\Classroom;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\Grade;
use App\Models\IssuedCertificate;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Storage::fake('public');

    // 1. Setup Academic Year & Class
    $this->academicYear = AcademicYear::firstOrCreate(
        ['year' => '2026/2027', 'semester' => 'Ganjil'],
        ['is_active' => true]
    );

    $this->classroom = Classroom::factory()->create(['grade_level' => 'XII']);
    $this->subject = Subject::factory()->create();

    // 2. Setup Roles & Users
    $this->adminUser = User::factory()->create(['role' => 'admin']);
    $this->wakaUser = User::factory()->create(['role' => 'guru', 'name' => 'Waka Kesiswaan']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Kemal Hafiz']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '110045',
        'nisn' => '0061234567',
    ]);

    // 3. Setup Ekskul Aktif Siswa
    $this->ekskul = Extracurricular::create([
        'name' => 'Rohis Sultan Agung 1',
        'category' => 'Keislaman',
        'advisor_id' => $this->wakaUser->id,
        'is_active' => true,
        'is_registration_open' => true,
    ]);

    ExtracurricularMember::create([
        'extracurricular_id' => $this->ekskul->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // 4. Setup Nilai Rapor Siswa
    Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'score' => 92.5,
        'type' => 'pas',
        'recorded_by' => $this->wakaUser->id,
    ]);
});

// ─────────────────────────────────────────────────────────────────────────────
// 1. TEST ENDPOINT AUTH & ROLE ACCESS
// ─────────────────────────────────────────────────────────────────────────────

test('tamu tanpa autentikasi ditolak 401 saat mengakses API prestasi', function () {
    $response = $this->getJson('/api/v1/achievements/list');
    $response->assertStatus(401);
});

test('peran parent ditolak 403 saat mengakses API prestasi mobile', function () {
    Sanctum::actingAs($this->parentUser);

    $response = $this->getJson('/api/v1/achievements/list');
    $response->assertStatus(403);
});

test('peran siswa dan guru diizinkan mengakses daftar prestasi', function () {
    Sanctum::actingAs($this->studentUser);
    $this->getJson('/api/v1/achievements/list')->assertStatus(200);

    Sanctum::actingAs($this->wakaUser);
    $this->getJson('/api/v1/achievements/list')->assertStatus(200);
});

// ─────────────────────────────────────────────────────────────────────────────
// 2. TEST UPLOAD PRESTASI (BASE64 & MULTIPART FORM-DATA)
// ─────────────────────────────────────────────────────────────────────────────

test('siswa dapat mengunggah prestasi baru via payload Base64', function () {
    Sanctum::actingAs($this->studentUser);

    $payload = [
        'title' => 'Juara 1 Lomba Karya Ilmiah Remaja (LKIR) Nasional',
        'field' => 'Matematika & Sains',
        'level' => 'NASIONAL',
        'organizer' => 'Badan Riset dan Inovasi Nasional (BRIN)',
        'date' => '2026-08-10',
        'image_base64' => base64_encode('fake_image_content_certificate'),
    ];

    $response = $this->postJson('/api/v1/achievements/upload', $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'title',
            'field',
            'level',
            'organizer',
            'date',
            'verification_status',
            'points_earned',
        ])
        ->assertJson([
            'title' => 'Juara 1 Lomba Karya Ilmiah Remaja (LKIR) Nasional',
            'level' => 'NASIONAL',
            'verification_status' => 'PENDING_REVIEW',
            'points_earned' => 50, // 50 poin untuk tingkat Nasional
        ]);

    expect(Achievement::where('student_id', $this->student->id)
        ->where('title', 'Juara 1 Lomba Karya Ilmiah Remaja (LKIR) Nasional')
        ->exists())->toBeTrue();
});

test('siswa dapat mengunggah prestasi baru via multipart file PDF atau gambar', function () {
    Sanctum::actingAs($this->studentUser);

    $file = UploadedFile::fake()->create('sertifikat_osn.pdf', 1024, 'application/pdf');

    $response = $this->postJson('/api/v1/achievements/upload', [
        'title' => 'Juara 1 OSN Bidang Fisika Provinsi Jateng',
        'field' => 'Matematika & Sains',
        'level' => 'PROVINSI',
        'organizer' => 'BPTI Kemendikbud',
        'date' => '2026-07-20',
        'file' => $file,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'level' => 'PROVINSI',
            'points_earned' => 25, // 25 poin untuk tingkat Provinsi
            'verification_status' => 'PENDING_REVIEW',
        ]);

    $achievement = Achievement::where('student_id', $this->student->id)->latest()->first();
    expect($achievement->certificate_path)->not->toBeNull();
    Storage::disk('public')->assertExists($achievement->certificate_path);
});

test('upload ditolak jika berkas melebihi ukuran maksimal 5MB atau format tidak valid', function () {
    Sanctum::actingAs($this->studentUser);

    // Format tidak valid (e.g. .txt)
    $invalidFile = UploadedFile::fake()->create('file.txt', 100, 'text/plain');

    $response = $this->postJson('/api/v1/achievements/upload', [
        'title' => 'Juara Debat',
        'field' => 'Seni Budaya',
        'level' => 'KOTA',
        'organizer' => 'Dinas Pendidikan',
        'date' => '2026-06-15',
        'file' => $invalidFile,
    ]);

    $response->assertStatus(422);
});

// ─────────────────────────────────────────────────────────────────────────────
// 3. TEST APPROVAL WORKFLOW OLEH WAKA KESISWAAN / GURU
// ─────────────────────────────────────────────────────────────────────────────

test('waka kesiswaan dapat memvalidasi dan menyetujui prestasi siswa', function () {
    // Buat prestasi berstatus PENDING_REVIEW
    $achievement = Achievement::create([
        'student_id' => $this->student->id,
        'title' => 'Juara 2 MTQ Kota Semarang',
        'field' => 'Keagamaan & Tahfidz',
        'level' => 'KOTA',
        'organizer' => 'Kemenag',
        'event_date' => '2026-07-10',
        'verification_status' => 'PENDING_REVIEW',
        'points_earned' => 15,
    ]);

    // Waka Kesiswaan login
    Sanctum::actingAs($this->wakaUser);

    $response = $this->postJson("/api/v1/achievements/{$achievement->id}/verify", [
        'approve' => true,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Status verifikasi prestasi berhasil diperbarui.',
            'data' => [
                'verification_status' => 'VALIDATED',
            ],
        ]);

    $achievement->refresh();
    expect($achievement->verification_status)->toBe('VALIDATED');
    expect($achievement->verified_by)->toBe($this->wakaUser->id);
    expect($achievement->verified_at)->not->toBeNull();
});

test('waka kesiswaan dapat menolak prestasi siswa dengan alasan penolakan', function () {
    $achievement = Achievement::create([
        'student_id' => $this->student->id,
        'title' => 'Sertifikat Lomba Tidak Resmi',
        'field' => 'Olahraga',
        'level' => 'KOTA',
        'organizer' => 'Pihak Swasta',
        'event_date' => '2026-07-10',
        'verification_status' => 'PENDING_REVIEW',
        'points_earned' => 15,
    ]);

    Sanctum::actingAs($this->wakaUser);

    $response = $this->postJson("/api/v1/achievements/{$achievement->id}/verify", [
        'approve' => false,
        'rejection_reason' => 'Piagam lomba tidak memiliki tanda tangan resmi dari dinas terkait.',
    ]);

    $response->assertStatus(200);

    $achievement->refresh();
    expect($achievement->verification_status)->toBe('REJECTED');
    expect($achievement->rejection_reason)->toContain('tidak memiliki tanda tangan resmi');
});

test('siswa ditolak 403 saat mencoba memverifikasi prestasi mandiri', function () {
    $achievement = Achievement::create([
        'student_id' => $this->student->id,
        'title' => 'Prestasi Tes',
        'field' => 'Seni',
        'level' => 'KOTA',
        'organizer' => 'Panitia',
        'event_date' => '2026-05-01',
        'verification_status' => 'PENDING_REVIEW',
    ]);

    Sanctum::actingAs($this->studentUser);

    $response = $this->postJson("/api/v1/achievements/{$achievement->id}/verify", [
        'approve' => true,
    ]);

    $response->assertStatus(403);
});

// ─────────────────────────────────────────────────────────────────────────────
// 4. TEST E-SERTIFIKAT DIGITAL & ACADEMIC CV SUMMARY (SNBP)
// ─────────────────────────────────────────────────────────────────────────────

test('endpoint certificates mengembalikan daftar e-sertifikat digital siswa', function () {
    IssuedCertificate::create([
        'student_id' => $this->studentUser->id,
        'verification_code' => 'CERT/SA1/2026/089',
        'metadata' => [
            'title' => 'Sertifikat Kepanitiaan Sultan Agung Fest 2026',
            'role' => 'Koordinator Divisi IT',
        ],
        'issued_at' => now(),
    ]);

    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/achievements/certificates');

    $response->assertStatus(200);
    $certs = $response->json();
    expect(is_array($certs))->toBeTrue();
    expect(count($certs))->toBeGreaterThanOrEqual(1);

    expect($certs[0])->toHaveKeys([
        'id',
        'certificate_number',
        'title',
        'role',
        'issued_date',
        'blockchain_hash',
    ]);
});

test('endpoint academic-cv-summary mengagregasi data capaian siswa secara akurat', function () {
    // 1. Tambahkan 1 prestasi tervalidasi (25 poin)
    Achievement::create([
        'student_id' => $this->student->id,
        'title' => 'Prestasi Valid 1',
        'field' => 'Sains',
        'level' => 'PROVINSI',
        'organizer' => 'Kemendikbud',
        'event_date' => '2026-05-01',
        'verification_status' => 'VALIDATED',
        'points_earned' => 25,
    ]);

    // 2. Tambahkan 1 prestasi pending (50 poin) -> tidak boleh dihitung
    Achievement::create([
        'student_id' => $this->student->id,
        'title' => 'Prestasi Pending',
        'field' => 'Sains',
        'level' => 'NASIONAL',
        'organizer' => 'BRIN',
        'event_date' => '2026-06-01',
        'verification_status' => 'PENDING_REVIEW',
        'points_earned' => 50,
    ]);

    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/achievements/academic-cv-summary');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'student_name',
            'nisn',
            'gpa_average',
            'total_achievements',
            'total_reward_points',
            'extracurriculars',
        ])
        ->assertJson([
            'student_name' => 'Kemal Hafiz',
            'nisn' => '0061234567',
            'total_achievements' => 1, // Hanya yang VALIDATED
            'total_reward_points' => 25, // Hanya poin dari yang VALIDATED
        ]);

    expect($response->json('extracurriculars'))->toContain('Rohis Sultan Agung 1');
});
