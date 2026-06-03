<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

beforeEach(function () {
    $this->waka = User::factory()->create(['role' => 'waka_kurikulum', 'phone_number' => '081234567890']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'phone_number' => '081211112222']);
    $this->parentUser = User::factory()->create(['role' => 'parent', 'phone_number' => '087777777777']);

    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);
});

test('waka kurikulum can create an academic event', function () {
    $response = $this->actingAs($this->waka)->post(route('events.store'), [
        'title' => 'Ujian Akhir Semester',
        'description' => 'Ujian semester genap.',
        'event_type' => 'exam',
        'start_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'end_date' => now()->addDays(2)->addHours(2)->format('Y-m-d H:i:s'),
        'is_all_day' => false,
        'color' => '#ef4444',
        'target_role' => 'student',
        'target_classroom_id' => $this->classroom->id,
        'send_notification' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => 'Agenda berhasil dibuat!']);

    $this->assertDatabaseHas('academic_events', [
        'title' => 'Ujian Akhir Semester',
        'event_type' => 'exam',
        'target_classroom_id' => $this->classroom->id,
        'send_notification' => 1,
    ]);
});

test('student cannot create an academic event', function () {
    $response = $this->actingAs($this->studentUser)->post(route('events.store'), [
        'title' => 'Rencana Siswa',
        'event_type' => 'osis',
        'start_date' => now()->addDays(1)->format('Y-m-d H:i:s'),
        'color' => '#3b82f6',
    ]);

    $response->assertStatus(403);
});

test('exam overlap protection blocks double scheduling on same class and time', function () {
    // 1. Create first exam event
    AcademicEvent::create([
        'title' => 'Ujian Pertama',
        'event_type' => 'exam',
        'start_date' => '2026-06-15 08:00:00',
        'end_date' => '2026-06-15 10:00:00',
        'target_classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->waka->id,
    ]);

    // 2. Try to create overlapping exam on same classroom
    $response = $this->actingAs($this->waka)->post(route('events.store'), [
        'title' => 'Ujian Kedua Bentrok',
        'event_type' => 'exam',
        'start_date' => '2026-06-15 09:00:00',
        'end_date' => '2026-06-15 11:00:00',
        'color' => '#ef4444',
        'target_classroom_id' => $this->classroom->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonFragment([
        'message' => 'Jadwal ujian bentrok! Sudah ada ujian lain di kelas dan waktu yang sama.',
    ]);
});

test('api events filters target roles and classrooms dynamically', function () {
    // Create public event
    AcademicEvent::create([
        'title' => 'Upacara Bendera',
        'event_type' => 'ceremony',
        'start_date' => '2026-06-01 07:00:00',
        'end_date' => '2026-06-01 08:00:00',
        'target_role' => null,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->waka->id,
    ]);

    // Create staff-only event
    AcademicEvent::create([
        'title' => 'Rapat Guru & Staff',
        'event_type' => 'meeting',
        'start_date' => '2026-06-01 13:00:00',
        'end_date' => '2026-06-01 15:00:00',
        'target_role' => 'guru',
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->waka->id,
    ]);

    // Query events as student
    $response = $this->actingAs($this->studentUser)->get(route('api.academic-events', [
        'start' => '2026-05-30',
        'end' => '2026-06-05',
    ]));

    $response->assertStatus(200);
    $events = $response->json();

    // Student should see public ceremony, but NOT staff meeting
    $titles = collect($events)->pluck('title');
    expect($titles)->toContain('Upacara Bendera');
    expect($titles)->not->toContain('Rapat Guru & Staff');
});

test('artisan command sends H-1 event reminders via DB notifications and WhatsApp logs', function () {
    Notification::fake();

    // Create event tomorrow with send_notification = true
    $event = AcademicEvent::create([
        'title' => 'Ujian H-1 Besok',
        'event_type' => 'exam',
        'start_date' => now()->addDay()->setHour(9)->setMinute(0)->setSecond(0),
        'end_date' => now()->addDay()->setHour(11)->setMinute(0)->setSecond(0),
        'target_role' => 'student',
        'target_classroom_id' => $this->classroom->id,
        'send_notification' => true,
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->waka->id,
    ]);

    // Execute artisan command
    $this->artisan('app:send-event-reminders')
        ->assertExitCode(0);

    // Verify student user received DB notification
    Notification::assertSentTo($this->studentUser, EventReminderNotification::class);

    // Verify WhatsApp log was created (mock sandbox mode will trigger automatic logging via WhatsappService)
    $this->assertDatabaseHas('whatsapp_logs', [
        'recipient' => '6281211112222',
        'type' => 'event_reminder',
        'status' => 'sent',
    ]);
});
