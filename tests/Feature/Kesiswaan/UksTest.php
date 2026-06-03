<?php

/** @var TestCase|stdClass $this */

use App\Models\Student;
use App\Models\UksMedicine;
use App\Models\UksVisit;
use App\Models\User;
use App\Models\WhatsappLog;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->parent = User::factory()->create(['role' => 'parent', 'phone_number' => '08123456789']);

    // Create student linked to the parent
    $this->student = Student::factory()->create([
        'parent_id' => $this->parent->id,
    ]);
});

// ── 1. ROLE-BASED ACCESS CONTROL (RBAC) ───────────────────────────────────

test('guru, bk, admin and staff can view UKS dashboard', function () {
    $roles = ['guru', 'bk', 'staf_tu', 'waka_kesiswaan', 'admin', 'superadmin'];

    foreach ($roles as $role) {
        $user = User::factory()->create(['role' => $role]);
        $response = $this->actingAs($user)->get(route('uks.dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('uks.dashboard');
    }
});

test('student cannot access UKS dashboard or management pages', function () {
    $studentUser = $this->student->user;

    $response = $this->actingAs($studentUser)->get(route('uks.dashboard'));
    $response->assertStatus(403);

    $response = $this->actingAs($studentUser)->get(route('uks.visits.index'));
    $response->assertStatus(403);
});

test('parent can view their child health history', function () {
    $response = $this->actingAs($this->parent)->get(route('uks.student-history', $this->student));

    $response->assertStatus(200);
    $response->assertViewIs('uks.student-history');
    $response->assertViewHas('student');
    $response->assertViewHas('visits');
});

test('parent cannot view another child health history', function () {
    $otherStudent = Student::factory()->create();

    $response = $this->actingAs($this->parent)->get(route('uks.student-history', $otherStudent));

    $response->assertStatus(403);
});

// ── 2. MEDICINE INVENTORY DEDUCTION ON VISIT ───────────────────────────────

test('recording a visit reduces medicine inventory stocks', function () {
    $medicine = UksMedicine::create([
        'name' => 'Ibuprofen 400mg',
        'category' => 'Obat Umum',
        'stock' => 15,
        'min_stock' => 3,
    ]);

    $response = $this->actingAs($this->guru)->post(route('uks.visits.store'), [
        'student_id' => $this->student->id,
        'visit_time' => now()->format('Y-m-d\TH:i'),
        'complaint' => 'Sakit Kepala',
        'severity' => 'ringan',
        'disposition' => 'istirahat_uks',
        'medicines' => [
            ['id' => $medicine->id, 'quantity' => 4],
        ],
    ]);

    $response->assertRedirect(route('uks.visits.index'));

    // Check medicine stock was decremented properly
    expect($medicine->refresh()->stock)->toBe(11);

    // Check database logs
    $this->assertDatabaseHas('uks_visits', [
        'student_id' => $this->student->id,
        'complaint' => 'Sakit Kepala',
    ]);
});

// ── 3. AUTOMATED WHATSAPP NOTIFICATION FOR EMERGENCY ───────────────────────

test('recording an emergency visit triggers a WhatsApp notification to parents', function () {
    $response = $this->actingAs($this->guru)->post(route('uks.visits.store'), [
        'student_id' => $this->student->id,
        'visit_time' => now()->format('Y-m-d\TH:i'),
        'complaint' => 'Sesak Nafas Akut',
        'severity' => 'berat',
        'disposition' => 'rujuk_rs',
    ]);

    $response->assertRedirect(route('uks.visits.index'));

    $visit = UksVisit::where('student_id', $this->student->id)->latest()->first();

    // Check notification flag
    expect($visit->parent_notified)->toBeTrue();

    // Check WhatsappLog creation (simulated in sandbox mode)
    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '628123456789',
        'type' => 'uks_emergency',
        'status' => 'sent',
    ]);
});

test('recording a light visit does not trigger a WhatsApp notification', function () {
    $response = $this->actingAs($this->guru)->post(route('uks.visits.store'), [
        'student_id' => $this->student->id,
        'visit_time' => now()->format('Y-m-d\TH:i'),
        'complaint' => 'Lecet Lutut Ringan',
        'severity' => 'ringan',
        'disposition' => 'kembali_kelas',
    ]);

    $response->assertRedirect(route('uks.visits.index'));

    $visit = UksVisit::where('student_id', $this->student->id)->latest()->first();

    expect($visit->parent_notified)->toBeFalse();
});

// ── 4. MEDICINE STOCK REVERSION ON CANCEL/DELETE ───────────────────────────

test('deleting a visit restores medicine inventory stocks', function () {
    $medicine = UksMedicine::create([
        'name' => 'Antasida Doen',
        'category' => 'Obat Umum',
        'stock' => 20,
        'min_stock' => 5,
    ]);

    // Create a visit with medicines
    $visit = UksVisit::create([
        'student_id' => $this->student->id,
        'handled_by' => $this->guru->id,
        'visit_time' => now(),
        'complaint' => 'Sakit Lambung',
        'severity' => 'ringan',
        'disposition' => 'istirahat_uks',
    ]);

    $visit->medicines()->attach($medicine->id, ['quantity' => 5]);
    $medicine->decrement('stock', 5);

    expect($medicine->refresh()->stock)->toBe(15);

    // Cancel/delete the visit
    $response = $this->actingAs($this->guru)->delete(route('uks.visits.destroy', $visit->uuid));

    $response->assertRedirect(route('uks.visits.index'));

    // Stock should be restored to 20
    expect($medicine->refresh()->stock)->toBe(20);
    $this->assertDatabaseMissing('uks_visits', ['id' => $visit->id]);
});

// ── 5. PDF MONTHLY REPORT EXPORT ───────────────────────────────────────────

test('authorized users can download monthly PDF report', function () {
    $response = $this->actingAs($this->guru)->get(route('uks.export.monthly', [
        'month' => now()->month,
        'year' => now()->year,
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});
