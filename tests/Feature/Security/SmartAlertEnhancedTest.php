<?php

use App\Models\AcademicYear;
use App\Models\Setting;
use App\Models\SmartAlert;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);

    $this->superadmin = User::factory()->create(['role' => 'superadmin', 'phone_number' => '081234567890']);
    $this->admin = User::factory()->create(['role' => 'admin', 'phone_number' => '081234567891']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'phone_number' => '081234567892']);
    $this->bk = User::factory()->create(['role' => 'bk', 'phone_number' => '081234567893']);
    $this->guru = User::factory()->create(['role' => 'guru', 'phone_number' => '081234567894']);
    $this->siswa = User::factory()->create(['role' => 'siswa']);

    $this->student = Student::factory()->create([
        'user_id' => $this->siswa->id,
    ]);

    // Active alert
    $this->alert = SmartAlert::create([
        'type' => 'critical_medicine',
        'severity' => 'warning',
        'title' => 'Stok Paracetamol Menipis',
        'description' => 'Sisa stok kurang dari batas minimum.',
        'alertable_type' => Student::class,
        'alertable_id' => $this->student->id,
        'status' => 'active',
    ]);
});

// ================================================================
// RBAC / ROLE-BASED ACCESS CONTROL TESTS
// ================================================================

test('admin, superadmin, kepsek can access smart alerts dashboard and configuration', function () {
    foreach ([$this->admin, $this->superadmin, $this->kepsek] as $user) {
        $this->actingAs($user)->get(route('smart-alerts.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('smart-alerts.rules'))->assertOk();
        $this->actingAs($user)->get(route('smart-alerts.history'))->assertOk();
    }
});

test('bk, guru, and students cannot access dashboard and rules', function () {
    foreach ([$this->bk, $this->guru, $this->siswa] as $user) {
        $this->actingAs($user)->get(route('smart-alerts.dashboard'))->assertStatus(403);
        $this->actingAs($user)->get(route('smart-alerts.rules'))->assertStatus(403);
    }
});

// ================================================================
// DYNAMIC RULES / CONFIGURATION TESTS
// ================================================================

test('admin can update threshold rules successfully', function () {
    $response = $this->actingAs($this->admin)->post(route('smart-alerts.rules.update'), [
        'smart_alert_at_risk_gpa_threshold' => '72.5',
        'smart_alert_at_risk_attendance_threshold' => '85',
        'smart_alert_at_risk_bk_points_threshold' => '40',
        'smart_alert_at_risk_bk_points_critical_threshold' => '60',
        'smart_alert_journal_gap_days' => '3',
        'smart_alert_journal_gap_critical_days' => '7',
        'smart_alert_medicine_expiry_days' => '45',
        'smart_alert_overdue_asset_days' => '5',
    ]);

    $response->assertRedirect(route('smart-alerts.rules'));
    expect((float) Setting::get('smart_alert_at_risk_gpa_threshold'))->toBe(72.5);
    expect((int) Setting::get('smart_alert_medicine_expiry_days'))->toBe(45);
});

// ================================================================
// MANUAL ALERTS TESTS
// ================================================================

test('bk and admin can access manual alert creation form', function () {
    $this->actingAs($this->admin)->get(route('smart-alerts.create'))->assertOk();
    $this->actingAs($this->bk)->get(route('smart-alerts.create'))->assertOk();
});

test('guru cannot access manual alert creation form', function () {
    $this->actingAs($this->guru)->get(route('smart-alerts.create'))->assertStatus(403);
});

test('bk can publish manual alert for a student and critical triggers notifications', function () {
    $response = $this->actingAs($this->bk)->post(route('smart-alerts.manual-create'), [
        'target_type' => 'student',
        'target_id' => $this->student->id,
        'severity' => 'critical',
        'title' => 'Perilaku Agresif Terdeteksi',
        'description' => 'Siswa bertengkar di kantin siang ini.',
    ]);

    $response->assertRedirect(route('smart-alerts.index'));

    $this->assertDatabaseHas('smart_alerts', [
        'type' => 'manual',
        'severity' => 'critical',
        'title' => 'Perilaku Agresif Terdeteksi',
        'alertable_type' => Student::class,
        'alertable_id' => $this->student->id,
        'status' => 'active',
    ]);

    // Check if WhatsApp log is created for critical alert (Sandbox Mode by default)
    $this->assertDatabaseHas('whatsapp_logs', [
        'type' => 'smart_alert_manual',
        'status' => 'sent',
    ]);
});

// ================================================================
// ESCALATION TESTS
// ================================================================

test('authorized staff can escalate alert to a supervisor and trigger notifications', function () {
    $response = $this->actingAs($this->bk)->post(route('smart-alerts.escalate', $this->alert), [
        'supervisor_id' => $this->kepsek->id,
        'escalation_notes' => 'Stok obat penting menipis dan belum ada tindakan dari staf UKS.',
    ]);

    $response->assertRedirect(route('smart-alerts.index'));

    $this->alert->refresh();
    expect($this->alert->severity)->toBe('critical');
    expect($this->alert->escalated_to_id)->toBe($this->kepsek->id);
    expect($this->alert->escalation_notes)->toBe('Stok obat penting menipis dan belum ada tindakan dari staf UKS.');
    expect($this->alert->escalated_at)->not->toBeNull();

    // Check if WhatsApp log is created for supervisor
    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '6281234567892', // normalized format of kepsek's phone
        'type' => 'smart_alert_escalation',
        'status' => 'sent',
    ]);
});

// ================================================================
// PDF EXPORT TESTS
// ================================================================

test('admin can export active alerts report to PDF', function () {
    $response = $this->actingAs($this->admin)->get(route('smart-alerts.export'));
    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
