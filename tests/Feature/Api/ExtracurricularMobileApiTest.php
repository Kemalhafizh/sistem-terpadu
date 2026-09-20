<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularMember;
use App\Models\OsisAnnouncement;
use App\Models\Student;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // 1. Setup Academic Year & Class
    $this->academicYear = AcademicYear::firstOrCreate(
        ['year' => '2026/2027', 'semester' => 'Ganjil'],
        ['is_active' => true]
    );

    $this->classroom = Classroom::factory()->create(['grade_level' => 'XI']);

    // 2. Setup Roles & Users
    $this->adminUser = User::factory()->create(['role' => 'admin']);
    $this->coachGuru = User::factory()->create(['role' => 'guru', 'name' => 'Ust. M. Rizqi, Lc.']);
    $this->otherGuru = User::factory()->create(['role' => 'guru', 'name' => 'Guru Lain, S.Pd.']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '109' . rand(1000, 9999),
    ]);

    // 3. Setup Extracurriculars
    $this->ekskulRohis = Extracurricular::create([
        'name' => 'Rohis Sultan Agung 1 (Karisma)',
        'category' => 'Keislaman & Da\'wah',
        'advisor_id' => $this->coachGuru->id,
        'coach_name' => 'Ust. M. Rizqi, Lc., M.Hum',
        'schedule_day' => 'Jumat',
        'schedule_time' => '15:30 - 17:00',
        'location' => 'Masjid Sultan Agung Lt. 2',
        'description' => 'Pembinaan kepribadian islami dan kajian remaja.',
        'max_members' => 30,
        'is_active' => true,
        'is_registration_open' => true,
    ]);

    $this->ekskulRobotik = Extracurricular::create([
        'name' => 'Klub Robotik & AI Sultan Agung',
        'category' => 'Sains & IT',
        'advisor_id' => $this->coachGuru->id,
        'coach_name' => 'Ir. Tri Wahyudi, M.T',
        'schedule_day' => 'Rabu',
        'schedule_time' => '15:30 - 17:30',
        'location' => 'Lab IoT & Robotika',
        'description' => 'Pengembangan robotika dan AI.',
        'max_members' => 20,
        'is_active' => true,
        'is_registration_open' => true,
    ]);

    // 4. Setup OSIS Posts
    $this->osisPost1 = OsisAnnouncement::create([
        'title' => '🔥 Pembukaan Pendaftaran Sultan Agung Cup & Class Meeting 2026',
        'author' => 'Divisi Olahraga & Seni OSIS',
        'category' => 'Class Meeting',
        'content' => 'Siapkan tim terbaik kelasmu!',
        'likes_count' => 245,
        'is_published' => true,
        'published_at' => now()->subDays(1),
    ]);

    $this->osisPost2 = OsisAnnouncement::create([
        'title' => '🗳️ Pengumuman Bakal Calon Ketua OSIS 2026/2027',
        'author' => 'Komisi Pemilihan OSIS (KPO)',
        'category' => 'Pemilu OSIS',
        'content' => 'Debat Terbuka Visi & Misi Paslon.',
        'likes_count' => 189,
        'is_published' => true,
        'published_at' => now()->subDays(3),
    ]);
});

// ─────────────────────────────────────────────────────────────────────────────
// 1. TEST ENDPOINT AUTH & ROLE ACCESS
// ─────────────────────────────────────────────────────────────────────────────

test('tamu tanpa autentikasi ditolak 401 saat mengakses API ekstrakurikuler', function () {
    $response = $this->getJson('/api/v1/extracurricular/list');
    $response->assertStatus(401);
});

test('peran parent ditolak dengan kode 403 saat mengakses API ekstrakurikuler mobile', function () {
    Sanctum::actingAs($this->parentUser);

    $response = $this->getJson('/api/v1/extracurricular/list');
    $response->assertStatus(403);
});

test('peran siswa dan guru diizinkan mengakses daftar ekstrakurikuler', function () {
    Sanctum::actingAs($this->studentUser);
    $resStudent = $this->getJson('/api/v1/extracurricular/list');
    $resStudent->assertStatus(200);

    Sanctum::actingAs($this->coachGuru);
    $resGuru = $this->getJson('/api/v1/extracurricular/list');
    $resGuru->assertStatus(200);
});

// ─────────────────────────────────────────────────────────────────────────────
// 2. TEST PENDAFTARAN EKSTRAKURIKULER & 4 ATURAN BISNIS
// ─────────────────────────────────────────────────────────────────────────────

test('siswa dapat mendaftar ke ekstrakurikuler yang terbuka', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->postJson("/api/v1/extracurricular/{$this->ekskulRohis->id}/register");

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Berhasil mendaftar di kegiatan ekstrakurikuler.',
        ]);

    expect(ExtracurricularMember::where('student_id', $this->student->id)
        ->where('extracurricular_id', $this->ekskulRohis->id)
        ->where('status', 'active')
        ->exists())->toBeTrue();
});

test('pendaftaran ditolak jika siswa sudah terdaftar aktif di ekskul tersebut', function () {
    Sanctum::actingAs($this->studentUser);

    // Daftar pertama kali
    $this->postJson("/api/v1/extracurricular/{$this->ekskulRohis->id}/register")->assertStatus(201);

    // Daftar kedua kali di ekskul yang sama
    $response = $this->postJson("/api/v1/extracurricular/{$this->ekskulRohis->id}/register");
    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => 'Anda sudah terdaftar aktif di ekstrakurikuler ini.',
        ]);
});

test('pendaftaran ditolak jika siswa telah mencapai batas maksimal 2 ekstrakurikuler', function () {
    Sanctum::actingAs($this->studentUser);

    // Buat 2 ekskul terdaftar
    ExtracurricularMember::create([
        'extracurricular_id' => $this->ekskulRohis->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    ExtracurricularMember::create([
        'extracurricular_id' => $this->ekskulRobotik->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Buat ekskul ke-3
    $ekskul3 = Extracurricular::create([
        'name' => 'Basket Sula',
        'category' => 'Olahraga',
        'advisor_id' => $this->coachGuru->id,
        'is_active' => true,
        'is_registration_open' => true,
    ]);

    // Coba daftar ekskul ke-3
    $response = $this->postJson("/api/v1/extracurricular/{$ekskul3->id}/register");
    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => 'Anda telah mencapai batas maksimal pendaftaran (maksimal 2 ekstrakurikuler).',
        ]);
});

test('pendaftaran ditolak jika kuota anggota ekskul telah penuh', function () {
    Sanctum::actingAs($this->studentUser);

    // Buat ekskul berkuota 1 dan sudah ada 1 anggota
    $ekskulFull = Extracurricular::create([
        'name' => 'Paskibra Inti',
        'category' => 'Kepemimpinan',
        'advisor_id' => $this->coachGuru->id,
        'max_members' => 1,
        'is_active' => true,
        'is_registration_open' => true,
    ]);

    $otherStudent = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    ExtracurricularMember::create([
        'extracurricular_id' => $ekskulFull->id,
        'student_id' => $otherStudent->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $response = $this->postJson("/api/v1/extracurricular/{$ekskulFull->id}/register");
    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => 'Pendaftaran gagal, kuota anggota ekstrakurikuler ini telah penuh.',
        ]);
});

test('pendaftaran ditolak jika periode pendaftaran sedang ditutup', function () {
    Sanctum::actingAs($this->studentUser);

    $this->ekskulRohis->update(['is_registration_open' => false]);

    $response = $this->postJson("/api/v1/extracurricular/{$this->ekskulRohis->id}/register");
    $response->assertStatus(422)
        ->assertJsonFragment([
            'message' => 'Pendaftaran untuk ekstrakurikuler ini sedang ditutup.',
        ]);
});

// ─────────────────────────────────────────────────────────────────────────────
// 3. TEST PRESENSI LATIHAN & OTORISASI PEMBINA
// ─────────────────────────────────────────────────────────────────────────────

test('pembina dapat mencatat presensi latihan untuk siswa anggota aktif', function () {
    // Daftarkan siswa ke ekskul Rohis
    ExtracurricularMember::create([
        'extracurricular_id' => $this->ekskulRohis->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Pembina Rohis login
    Sanctum::actingAs($this->coachGuru);

    $response = $this->postJson('/api/v1/extracurricular/attendance/submit', [
        'ekskul_id' => $this->ekskulRohis->id,
        'student_id' => $this->student->id,
        'qr_payload' => 'ROHIS-SESSION-2026-09-18',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Presensi latihan berhasil dicatat.',
        ]);

    expect(ExtracurricularAttendance::where('extracurricular_id', $this->ekskulRohis->id)
        ->where('student_id', $this->student->id)
        ->whereDate('date', now()->toDateString())
        ->where('status', 'H')
        ->exists())->toBeTrue();
});

test('siswa ditolak dengan kode 403 saat mencoba mencatat presensi', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->postJson('/api/v1/extracurricular/attendance/submit', [
        'ekskul_id' => $this->ekskulRohis->id,
        'student_id' => $this->student->id,
    ]);

    $response->assertStatus(403)
        ->assertJsonFragment([
            'message' => 'Hanya pembina atau pelatih ekstrakurikuler yang dapat mencatat presensi.',
        ]);
});

test('guru yang bukan pembina ekskul bersangkutan ditolak dengan kode 403', function () {
    Sanctum::actingAs($this->otherGuru);

    $response = $this->postJson('/api/v1/extracurricular/attendance/submit', [
        'ekskul_id' => $this->ekskulRohis->id,
        'student_id' => $this->student->id,
    ]);

    $response->assertStatus(403)
        ->assertJsonFragment([
            'message' => 'Anda bukan pembina yang ditugaskan untuk ekstrakurikuler ini.',
        ]);
});

test('presensi gagal jika siswa bukan anggota aktif ekskul tersebut', function () {
    Sanctum::actingAs($this->coachGuru);

    // Siswa belum didaftarkan
    $response = $this->postJson('/api/v1/extracurricular/attendance/submit', [
        'ekskul_id' => $this->ekskulRohis->id,
        'student_id' => $this->student->id,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

// ─────────────────────────────────────────────────────────────────────────────
// 4. TEST KATALOG EKSKUL & OSIS FEED
// ─────────────────────────────────────────────────────────────────────────────

test('katalog ekskul mengembalikan JSON array langsung yang kompatibel dengan Retrofit Android', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/extracurricular/list');
    $response->assertStatus(200);

    $ekskuls = $response->json();
    expect(is_array($ekskuls))->toBeTrue();
    expect(count($ekskuls))->toBeGreaterThanOrEqual(2);

    $first = $ekskuls[0];
    expect($first)->toHaveKeys([
        'id',
        'name',
        'category',
        'coach_name',
        'training_schedule',
        'location',
        'description',
        'member_count',
        'is_registered',
    ]);
});

test('flag is_registered pada katalog ekskul akurat sesuai status keikutsertaan siswa', function () {
    // Daftarkan siswa hanya ke Rohis
    ExtracurricularMember::create([
        'extracurricular_id' => $this->ekskulRohis->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/extracurricular/list');
    $response->assertStatus(200);

    $ekskuls = collect($response->json());
    $rohisItem = $ekskuls->firstWhere('id', $this->ekskulRohis->id);
    $robotikItem = $ekskuls->firstWhere('id', $this->ekskulRobotik->id);

    expect($rohisItem['is_registered'])->toBeTrue();
    expect($robotikItem['is_registered'])->toBeFalse();
});

test('feed OSIS mengembalikan daftar pengumuman terbitan dengan urutan terbaru', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/extracurricular/osis-feed');
    $response->assertStatus(200);

    $posts = $response->json();
    expect(is_array($posts))->toBeTrue();
    expect(count($posts))->toBe(2);

    $first = $posts[0];
    expect($first)->toHaveKeys([
        'id',
        'title',
        'author',
        'category',
        'date',
        'content',
        'likes_count',
    ]);

    // Post 1 lebih baru daripada Post 2
    expect($first['id'])->toBe($this->osisPost1->id);
});

test('feed OSIS mendukung mode paginasi saat parameter paginated dikirim', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson('/api/v1/extracurricular/osis-feed?paginated=true&per_page=1');
    $response->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data',
            'total',
            'per_page',
        ]);

    expect(count($response->json('data')))->toBe(1);
});
