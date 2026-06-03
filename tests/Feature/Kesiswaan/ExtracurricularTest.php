<?php

/** @var TestCase|stdClass $this */

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularActivity;
use App\Models\OsisTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    // Seed essential roles and models
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->waka = User::factory()->create(['role' => 'waka_kesiswaan']);
    $this->teacher = User::factory()->create(['role' => 'guru']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
    
    $this->classroom = Classroom::factory()->create();
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '12345'
    ]);
});

// ── 1. GENERAL READ ACCESS ──────────────────────────────────────────────────

test('students and authorized roles can view extracurriculars list', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('ekskul.index'));
    $response->assertStatus(200);
    $response->assertViewIs('extracurricular.index');
    $response->assertViewHas('extracurriculars');
});

test('students and authorized roles can view detailed extracurricular profile', function () {
    $ekskul = Extracurricular::create([
        'name' => 'Futsal Club',
        'advisor_id' => $this->teacher->id,
        'max_members' => 20,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->studentUser)
        ->get(route('ekskul.show', $ekskul->id));
    
    $response->assertStatus(200);
    $response->assertViewIs('extracurricular.show');
    $response->assertViewHas('extracurricular');
});

// ── 2. REGISTRATION LIMITS AND SLOT CHECKS ──────────────────────────────────

test('students can register to extracurricular and available slots are updated', function () {
    $ekskul = Extracurricular::create([
        'name' => 'Robotik',
        'advisor_id' => $this->teacher->id,
        'max_members' => 15,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->studentUser)
        ->post(route('ekskul.register', $ekskul->id));

    $response->assertRedirect(route('ekskul.show', $ekskul->id));
    $response->assertSessionHas('success');

    expect(ExtracurricularMember::where('extracurricular_id', $ekskul->id)->count())->toBe(1);
    expect($ekskul->fresh()->available_slots)->toBe(14);
});

test('student registration is blocked if slot quota is full', function () {
    $ekskul = Extracurricular::create([
        'name' => 'Pramuka Garuda',
        'advisor_id' => $this->teacher->id,
        'max_members' => 1,
        'is_active' => true,
    ]);

    // Fill the only slot manually with another student
    $otherStudent = Student::factory()->create();
    ExtracurricularMember::create([
        'extracurricular_id' => $ekskul->id,
        'student_id' => $otherStudent->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Try registering the current student
    $response = $this->actingAs($this->studentUser)
        ->post(route('ekskul.register', $ekskul->id));

    $response->assertSessionHas('error', 'Pendaftaran gagal, kuota anggota ekstrakurikuler ini telah penuh.');
    expect(ExtracurricularMember::where('extracurricular_id', $ekskul->id)->count())->toBe(1);
});

test('student registration is restricted to max 2 extracurriculars', function () {
    $ekskul1 = Extracurricular::create(['name' => 'Ekskul 1', 'advisor_id' => $this->teacher->id, 'is_active' => true]);
    $ekskul2 = Extracurricular::create(['name' => 'Ekskul 2', 'advisor_id' => $this->teacher->id, 'is_active' => true]);
    $ekskul3 = Extracurricular::create(['name' => 'Ekskul 3', 'advisor_id' => $this->teacher->id, 'is_active' => true]);

    ExtracurricularMember::create(['extracurricular_id' => $ekskul1->id, 'student_id' => $this->student->id, 'status' => 'active', 'joined_at' => now()]);
    ExtracurricularMember::create(['extracurricular_id' => $ekskul2->id, 'student_id' => $this->student->id, 'status' => 'active', 'joined_at' => now()]);

    $response = $this->actingAs($this->studentUser)
        ->post(route('ekskul.register', $ekskul3->id));

    $response->assertSessionHas('error', 'Anda telah mencapai batas maksimal pendaftaran (maksimal 2 ekstrakurikuler).');
    expect(ExtracurricularMember::where('student_id', $this->student->id)->count())->toBe(2);
});

// ── 3. ATTENDANCE AND ACTIVITY DOCUMENTATION ───────────────────────────────

test('advisor and admin can submit batch attendance', function () {
    $ekskul = Extracurricular::create([
        'name' => 'Paskibra',
        'advisor_id' => $this->teacher->id,
        'is_active' => true,
    ]);
    
    ExtracurricularMember::create([
        'extracurricular_id' => $ekskul->id,
        'student_id' => $this->student->id,
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $data = [
        'extracurricular_id' => $ekskul->id,
        'date' => now()->toDateString(),
        'attendance' => [
            [
                'student_id' => $this->student->id,
                'status' => 'H',
                'notes' => 'Hadir tepat waktu dan aktif',
            ]
        ]
    ];

    $response = $this->actingAs($this->teacher)
        ->post(route('ekskul.attendance.store'), $data);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    expect(ExtracurricularAttendance::where('extracurricular_id', $ekskul->id)->count())->toBe(1);
    $attendanceRecord = ExtracurricularAttendance::first();
    expect($attendanceRecord->student_id)->toBe($this->student->id);
    expect($attendanceRecord->status)->toBe('H');
});

test('other roles cannot submit batch attendance for extracurricular they do not advise', function () {
    $ekskul = Extracurricular::create([
        'name' => 'Futsal',
        'advisor_id' => $this->teacher->id, // Advisor is $this->teacher
        'is_active' => true,
    ]);

    $otherTeacher = User::factory()->create(['role' => 'guru']);

    $data = [
        'extracurricular_id' => $ekskul->id,
        'date' => now()->toDateString(),
        'attendance' => [
            [
                'student_id' => $this->student->id,
                'status' => 'H',
            ]
        ]
    ];

    $response = $this->actingAs($otherTeacher)
        ->post(route('ekskul.attendance.store'), $data);

    $response->assertStatus(403);
});

test('advisor and admin can submit extracurricular activity logs with image uploads', function () {
    Storage::fake('public');
    
    $ekskul = Extracurricular::create([
        'name' => 'Seni Musik',
        'advisor_id' => $this->teacher->id,
        'is_active' => true,
    ]);

    $file1 = UploadedFile::fake()->image('activity1.jpg', 800, 600);
    $file2 = UploadedFile::fake()->image('activity2.png', 800, 600);

    $data = [
        'extracurricular_id' => $ekskul->id,
        'title' => 'Latihan Rutin Karawitan',
        'description' => 'Mempelajari gending Ladrang Wilujeng pelog pathet barang.',
        'activity_date' => now()->toDateString(),
        'images' => [$file1, $file2]
    ];

    $response = $this->actingAs($this->teacher)
        ->post(route('ekskul.activities.store'), $data);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    expect(ExtracurricularActivity::where('extracurricular_id', $ekskul->id)->count())->toBe(1);
    
    $activity = ExtracurricularActivity::first();
    expect($activity->title)->toBe('Latihan Rutin Karawitan');
    expect(count($activity->gallery_paths))->toBe(2);

    Storage::disk('public')->assertExists($activity->gallery_paths[0]);
    Storage::disk('public')->assertExists($activity->gallery_paths[1]);
});

// ── 4. OSIS FINANCIAL LEDGER & PDF EXPORTS ─────────────────────────────────

test('waka kesiswaan and admin can access OSIS financial dashboard', function () {
    $response = $this->actingAs($this->waka)
        ->get(route('osis.dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('extracurricular.osis.dashboard');
    $response->assertViewHasAll(['transactions', 'totalIncome', 'totalExpense', 'currentBalance']);
});

test('unauthorized roles are forbidden from OSIS financial dashboard', function () {
    $response = $this->actingAs($this->studentUser)
        ->get(route('osis.dashboard'));
    $response->assertStatus(403);

    $response = $this->actingAs($this->teacher)
        ->get(route('osis.dashboard'));
    $response->assertStatus(403);
});

test('waka kesiswaan and admin can log OSIS financial transaction with receipt scan', function () {
    Storage::fake('public');
    $receipt = UploadedFile::fake()->create('kuitansi_belanja.png', 500); // 500KB

    $data = [
        'type' => 'expense',
        'amount' => 1250000,
        'description' => 'Pembelian Trofi Piala OSIS Cup 2026',
        'category' => 'Kegiatan',
        'transaction_date' => now()->toDateString(),
        'receipt' => $receipt
    ];

    $response = $this->actingAs($this->waka)
        ->post(route('osis.transactions.store'), $data);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    expect(OsisTransaction::count())->toBe(1);
    $transaction = OsisTransaction::first();
    expect($transaction->type)->toBe('expense');
    expect((int)$transaction->amount)->toBe(1250000);
    expect($transaction->category)->toBe('Kegiatan');
    
    expect($transaction->receipt_path)->not->toBeNull();
    Storage::disk('public')->assertExists($transaction->receipt_path);
});

test('waka kesiswaan and admin can export OSIS ledger transaction list to PDF', function () {
    // Seed a couple of transactions
    OsisTransaction::create([
        'type' => 'income',
        'amount' => 5000000,
        'description' => 'Iuran Bulanan Kelas X, XI, XII',
        'category' => 'Iuran',
        'transaction_date' => now()->startOfMonth(),
        'recorded_by' => $this->waka->id,
    ]);

    OsisTransaction::create([
        'type' => 'expense',
        'amount' => 1500000,
        'description' => 'Sewa Sound System Latihan Band',
        'category' => 'Perlengkapan',
        'transaction_date' => now(),
        'recorded_by' => $this->waka->id,
    ]);

    $data = [
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->toDateString(),
    ];

    $response = $this->actingAs($this->waka)
        ->get(route('osis.export', $data));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
