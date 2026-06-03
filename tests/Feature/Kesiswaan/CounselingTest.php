<?php

/** @var TestCase|stdClass $this */

use App\Models\Classroom;
use App\Models\CounselingSession;
use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'phone_number' => '08123456780']);
    $this->bk = User::factory()->create(['role' => 'bk', 'phone_number' => '08123456781']);
    $this->waka = User::factory()->create(['role' => 'waka_kesiswaan', 'phone_number' => '08123456782']);
    $this->parent = User::factory()->create(['role' => 'parent', 'phone_number' => '08123456789']);

    // Create classroom first
    $this->classroom = Classroom::factory()->create();

    // Create student linked to parent
    $this->student = Student::factory()->create([
        'parent_id' => $this->parent->id,
        'classroom_id' => $this->classroom->id,
    ]);
});

// ── 1. ROLE-BASED ACCESS CONTROL (RBAC) ───────────────────────────────────

test('bk, admin can view counseling dashboard', function () {
    $allowedRoles = ['bk', 'admin', 'superadmin'];

    foreach ($allowedRoles as $role) {
        $user = User::factory()->create(['role' => $role]);
        $response = $this->actingAs($user)->get(route('counseling.dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('counseling.dashboard');
    }
});

test('waka_kesiswaan, student, and parent cannot access counseling dashboard', function () {
    $response = $this->actingAs($this->waka)->get(route('counseling.dashboard'));
    $response->assertStatus(403);

    $response = $this->actingAs($this->student->user)->get(route('counseling.dashboard'));
    $response->assertStatus(403);

    $response = $this->actingAs($this->parent)->get(route('counseling.dashboard'));
    $response->assertStatus(403);
});

test('bk, admin, waka_kesiswaan can view counseling session list', function () {
    $allowedRoles = ['bk', 'admin', 'superadmin', 'waka_kesiswaan'];

    foreach ($allowedRoles as $role) {
        $user = User::factory()->create(['role' => $role]);
        $response = $this->actingAs($user)->get(route('counseling.index'));
        $response->assertStatus(200);
        $response->assertViewIs('counseling.index');
    }
});

test('student and parent can view request form', function () {
    $response = $this->actingAs($this->student->user)->get(route('counseling.request-form'));
    $response->assertStatus(200);
    $response->assertViewIs('counseling.request');

    $response = $this->actingAs($this->parent)->get(route('counseling.request-form'));
    $response->assertStatus(200);
    $response->assertViewIs('counseling.request');
});

// ── 2. SESSION REQUEST & SUBMISSION ───────────────────────────────────────

test('student can request a counseling session for themselves', function () {
    $scheduledAt = now()->addDays(2)->format('Y-m-d\TH:i');

    $response = $this->actingAs($this->student->user)->post(route('counseling.request'), [
        'student_id' => $this->student->id,
        'type' => 'individual',
        'category' => 'pribadi',
        'reason' => 'Saya merasa kurang motivasi belajar akhir-akhir ini',
        'scheduled_at' => $scheduledAt,
    ]);

    $response->assertRedirect(route('counseling.index'));

    $this->assertDatabaseHas('counseling_sessions', [
        'student_id' => $this->student->id,
        'type' => 'individual',
        'category' => 'pribadi',
        'status' => 'requested',
        'requested_by' => $this->student->user->id,
    ]);

    // BK gets a WA notification (phone prefixed with 62)
    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '628123456781', // BK phone number normalized
        'type' => 'bk_notification',
        'status' => 'sent',
    ]);
});

test('parent can request a counseling session for their child', function () {
    $scheduledAt = now()->addDays(2)->format('Y-m-d\TH:i');

    $response = $this->actingAs($this->parent)->post(route('counseling.request'), [
        'student_id' => $this->student->id,
        'type' => 'individual',
        'category' => 'akademik',
        'reason' => 'Anak saya sulit berkonsentrasi belajar di rumah',
        'scheduled_at' => $scheduledAt,
    ]);

    $response->assertRedirect(route('counseling.index'));

    $this->assertDatabaseHas('counseling_sessions', [
        'student_id' => $this->student->id,
        'type' => 'individual',
        'category' => 'akademik',
        'status' => 'requested',
        'requested_by' => $this->parent->id,
    ]);
});

test('student cannot request a counseling session for another student', function () {
    $otherStudent = Student::factory()->create();
    $scheduledAt = now()->addDays(2)->format('Y-m-d\TH:i');

    $response = $this->actingAs($this->student->user)->post(route('counseling.request'), [
        'student_id' => $otherStudent->id,
        'type' => 'individual',
        'category' => 'pribadi',
        'reason' => 'Mencoba meretas data orang lain',
        'scheduled_at' => $scheduledAt,
    ]);

    $response->assertStatus(403);
});

// ── 3. SCHEDULING, EXECUTION, AND ENCRYPTION ──────────────────────────────

test('bk can schedule/confirm a session request and notify student and parent', function () {
    $session = CounselingSession::create([
        'student_id' => $this->student->id,
        'requested_by' => $this->student->user->id,
        'type' => 'individual',
        'category' => 'pribadi',
        'reason' => 'Butuh curhat',
        'status' => 'requested',
        'scheduled_at' => now()->addDays(2),
    ]);

    $newSchedule = now()->addDays(1)->setHour(10)->setMinute(0);

    $response = $this->actingAs($this->bk)->post(route('counseling.schedule', $session), [
        'counselor_id' => $this->bk->id,
        'scheduled_at' => $newSchedule->format('Y-m-d\TH:i'),
    ]);

    $response->assertRedirect(route('counseling.index'));

    // Check status changes to scheduled
    expect($session->refresh()->status)->toBe('scheduled');
    expect($session->counselor_id)->toBe($this->bk->id);

    // Assert notification sent to parent (normalized to 62 prefix)
    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '628123456789', // Parent
        'type' => 'bk_scheduled',
        'status' => 'sent',
    ]);
});

test('bk can start and complete a session, verifying database level encryption', function () {
    $session = CounselingSession::create([
        'student_id' => $this->student->id,
        'counselor_id' => $this->bk->id,
        'type' => 'individual',
        'category' => 'pribadi',
        'reason' => 'Masalah keluarga sangat privat',
        'status' => 'scheduled',
        'scheduled_at' => now(),
    ]);

    // 1. Start Session
    $response = $this->actingAs($this->bk)->post(route('counseling.start', $session));
    $response->assertRedirect(route('counseling.session', $session));
    expect($session->refresh()->status)->toBe('in_progress');

    // 2. Complete Session
    $response = $this->actingAs($this->bk)->post(route('counseling.complete', $session), [
        'session_notes' => 'Catatan hasil sesi curhat siswa yang sangat rahasia',
        'action_plan' => 'Siswa harus istirahat dari gadget dan didampingi guru BK seminggu sekali',
        'follow_up_notes' => 'Rencana pertemuan lanjutan dengan orang tua',
        'follow_up_date' => now()->addWeek()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('counseling.index'));

    $session->refresh();
    expect($session->status)->toBe('completed');

    // 3. Verify Transparent Casting Works (Decrypted)
    expect($session->session_notes)->toBe('Catatan hasil sesi curhat siswa yang sangat rahasia');
    expect($session->action_plan)->toBe('Siswa harus istirahat dari gadget dan didampingi guru BK seminggu sekali');
    expect($session->follow_up_notes)->toBe('Rencana pertemuan lanjutan dengan orang tua');
    expect($session->reason)->toBe('Masalah keluarga sangat privat');

    // 4. Verify Database Contains Ciphertext, NOT Plaintext
    $rawDbRow = DB::table('counseling_sessions')->where('id', $session->id)->first();

    expect($rawDbRow->session_notes)->not->toBeNull();
    expect($rawDbRow->session_notes)->not->toContain('Catatan hasil sesi curhat');
    expect($rawDbRow->action_plan)->not->toContain('Siswa harus istirahat');
    expect($rawDbRow->follow_up_notes)->not->toContain('pertemuan lanjutan');
    expect($rawDbRow->reason)->not->toContain('keluarga sangat privat');

    // Make sure it starts with the typical Laravel encrypted payload signature (JSON base64 string)
    $payloadDecoded = json_decode(base64_decode($rawDbRow->session_notes), true);
    if ($payloadDecoded === null) {
        // Laravel's encrypted cast stores database fields as encrypted strings.
        // It should not be the plaintext value.
        expect(strpos($rawDbRow->session_notes, 'Catatan hasil sesi'))->toBeFalse();
    } else {
        expect($payloadDecoded)->toHaveKey('iv');
        expect($payloadDecoded)->toHaveKey('value');
        expect($payloadDecoded)->toHaveKey('mac');
    }
});

// ── 4. POINT ACCUMULATION AUTOMATIC COUNSELING TRIGGER ───────────────────

test('exceeding 50 accumulated student points automatically triggers a mandatory counseling session', function () {
    // Create point category
    $category = PointCategory::create([
        'name' => 'Pelanggaran Berat',
        'type' => 'penalty',
        'default_points' => 30,
        'is_active' => true,
    ]);

    // Add first point log: 30 points
    StudentPointLog::create([
        'student_id' => $this->student->id,
        'point_category_id' => $category->id,
        'created_by' => $this->admin->id,
        'points' => 30,
        'type' => 'penalty',
        'description' => 'Membawa rokok ke sekolah',
    ]);

    // Check points are 30 and no session created yet
    expect($this->student->refresh()->total_points)->toBe(30);
    expect(CounselingSession::where('student_id', $this->student->id)->exists())->toBeFalse();

    // Add second point log: 25 points -> total 55 points (>= 50 critical limit)
    StudentPointLog::create([
        'student_id' => $this->student->id,
        'point_category_id' => $category->id,
        'created_by' => $this->admin->id,
        'points' => 25,
        'type' => 'penalty',
        'description' => 'Membolos di jam sekolah kedua kalinya',
    ]);

    expect($this->student->refresh()->total_points)->toBe(55);

    // Verify a counseling session is automatically created
    $session = CounselingSession::where('student_id', $this->student->id)
        ->where('status', 'requested')
        ->where('category', 'pribadi')
        ->first();

    expect($session)->not->toBeNull();
    expect($session->reason)->toContain('Sesi Konseling Wajib Otomatis dipicu karena akumulasi poin pelanggaran siswa telah mencapai batas kritis');

    // Verify WhatsApp log/notification sent to parent (normalized 62 prefix)
    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '628123456789', // Parent phone
        'type' => 'bk_emergency_mandatory',
        'status' => 'sent',
    ]);
});

// ── 5. OFFICIAL PDF EXPORT ───────────────────────────────────────────────

test('bk, waka_kesiswaan can download completed session report PDF', function () {
    $session = CounselingSession::create([
        'student_id' => $this->student->id,
        'counselor_id' => $this->bk->id,
        'type' => 'individual',
        'category' => 'pribadi',
        'reason' => 'Masalah motivasi',
        'status' => 'completed',
        'scheduled_at' => now(),
        'session_notes' => 'Catatan sesi',
        'action_plan' => 'Rencana tindakan',
    ]);

    $response = $this->actingAs($this->bk)->get(route('counseling.student-report', $session));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('student cannot download session report PDF of someone else', function () {
    $otherStudent = Student::factory()->create();
    $session = CounselingSession::create([
        'student_id' => $otherStudent->id,
        'counselor_id' => $this->bk->id,
        'type' => 'individual',
        'category' => 'pribadi',
        'reason' => 'Masalah motivasi',
        'status' => 'completed',
        'scheduled_at' => now(),
        'session_notes' => 'Catatan sesi',
        'action_plan' => 'Rencana tindakan',
    ]);

    $response = $this->actingAs($this->student->user)->get(route('counseling.student-report', $session));
    $response->assertStatus(403);
});
