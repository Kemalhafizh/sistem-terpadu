<?php

use App\Models\Classroom;
use App\Models\EventAttendanceEvent;
use App\Models\EventAttendanceRecord;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Admin user
    $this->adminUser = User::factory()->create(['role' => 'admin']);

    // Teacher user
    $this->teacherUser = User::factory()->create(['role' => 'guru']);

    // Another teacher (to test specific targeting or permissions)
    $this->otherTeacher = User::factory()->create(['role' => 'guru']);

    // Classrooms
    $this->classroomA = Classroom::factory()->create([
        'teacher_id' => $this->teacherUser->id,
        'name' => 'XII-IPA-1'
    ]);
    $this->classroomB = Classroom::factory()->create([
        'teacher_id' => $this->otherTeacher->id,
        'name' => 'XII-IPA-2'
    ]);

    // Student A (Classroom A) and parent
    $this->parentUserA = User::factory()->create(['role' => 'parent']);
    $this->studentUserA = User::factory()->create(['role' => 'student']);
    $this->studentA = Student::factory()->create([
        'user_id' => $this->studentUserA->id,
        'parent_id' => $this->parentUserA->id,
        'classroom_id' => $this->classroomA->id,
    ]);

    // Student B (Classroom B) and parent
    $this->parentUserB = User::factory()->create(['role' => 'parent']);
    $this->studentUserB = User::factory()->create(['role' => 'student']);
    $this->studentB = Student::factory()->create([
        'user_id' => $this->studentUserB->id,
        'parent_id' => $this->parentUserB->id,
        'classroom_id' => $this->classroomB->id,
    ]);
});

test('student redirects to rekap when viewing index', function () {
    $response = $this->actingAs($this->studentUserA)->get(route('event-attendance.index'));
    $response->assertRedirect(route('event-attendance.rekap'));
});

test('guest redirects to login when viewing index', function () {
    $response = $this->get(route('event-attendance.index'));
    $response->assertRedirect(route('login'));
});

test('guru can view event index page', function () {
    $response = $this->actingAs($this->teacherUser)->get(route('event-attendance.index'));
    $response->assertStatus(200);
    $response->assertViewHas('events');
});

test('guru can create event targeting all students and system generates draft records', function () {
    $response = $this->actingAs($this->teacherUser)->post(route('event-attendance.store'), [
        'name' => 'Sholat Dzuhur Berjamaah Bersama',
        'type' => 'sholat_dzuhur',
        'event_date' => now()->format('Y-m-d'),
        'start_time' => '12:00',
        'end_time' => '12:45',
        'target' => 'all_students',
    ]);

    $response->assertRedirect(route('event-attendance.index'));
    $response->assertSessionHas('success');

    $event = EventAttendanceEvent::where('name', 'Sholat Dzuhur Berjamaah Bersama')->first();
    expect($event)->not->toBeNull();
    expect($event->target)->toBe('all_students');

    // Should generate draft records with default 'alpa' status for all students
    $records = EventAttendanceRecord::where('event_id', $event->id)->get();
    // Student A and Student B should both have a record
    expect($records->count())->toBe(2);
    expect($records->where('user_id', $this->studentUserA->id)->first()->status)->toBe('alpa');
    expect($records->where('user_id', $this->studentUserB->id)->first()->status)->toBe('alpa');
});

test('guru can create event targeting specific classrooms only', function () {
    $response = $this->actingAs($this->teacherUser)->post(route('event-attendance.store'), [
        'name' => 'Kultum PAI Kelas XII-IPA-1',
        'type' => 'kultum',
        'event_date' => now()->format('Y-m-d'),
        'start_time' => '12:30',
        'end_time' => '13:00',
        'target' => 'specific_class',
        'target_ids' => [$this->classroomA->id], // Only classroom A
    ]);

    $response->assertRedirect(route('event-attendance.index'));
    
    $event = EventAttendanceEvent::where('name', 'Kultum PAI Kelas XII-IPA-1')->first();
    expect($event)->not->toBeNull();

    // Should generate draft record ONLY for Student A
    $records = EventAttendanceRecord::where('event_id', $event->id)->get();
    expect($records->count())->toBe(1);
    expect($records->where('user_id', $this->studentUserA->id)->first())->not->toBeNull();
    expect($records->where('user_id', $this->studentUserB->id)->first())->toBeNull();
});

test('guru can input and save batch attendance for an event', function () {
    $event = EventAttendanceEvent::factory()->create([
        'name' => 'Upacara Bendera Hari Senin',
        'type' => 'upacara',
        'event_date' => now()->format('Y-m-d'),
        'target' => 'all_students',
        'created_by' => $this->teacherUser->id,
    ]);

    // Create initial drafts
    EventAttendanceRecord::factory()->create([
        'event_id' => $event->id,
        'user_id' => $this->studentUserA->id,
        'status' => 'alpa',
    ]);
    EventAttendanceRecord::factory()->create([
        'event_id' => $event->id,
        'user_id' => $this->studentUserB->id,
        'status' => 'alpa',
    ]);

    $response = $this->actingAs($this->teacherUser)->post(route('event-attendance.store-attendance', $event->id), [
        'records' => [
            [
                'user_id' => $this->studentUserA->id,
                'status' => 'hadir',
                'check_in_time' => '07:05',
                'notes' => 'Tepat waktu',
            ],
            [
                'user_id' => $this->studentUserB->id,
                'status' => 'izin',
                'check_in_time' => null,
                'notes' => 'Ada acara keluarga',
            ],
        ],
    ]);

    $response->assertRedirect(route('event-attendance.show', $event->id));
    $response->assertSessionHas('success');

    $recordA = EventAttendanceRecord::where('event_id', $event->id)->where('user_id', $this->studentUserA->id)->first();
    expect($recordA->status)->toBe('hadir');
    expect(Carbon::parse($recordA->check_in_time)->format('H:i'))->toBe('07:05');
    expect($recordA->notes)->toBe('Tepat waktu');

    $recordB = EventAttendanceRecord::where('event_id', $event->id)->where('user_id', $this->studentUserB->id)->first();
    expect($recordB->status)->toBe('izin');
    expect($recordB->check_in_time)->toBeNull();
    expect($recordB->notes)->toBe('Ada acara keluarga');
});

test('student can view their own event attendance rekap but not other students', function () {
    $event = EventAttendanceEvent::factory()->create([
        'name' => 'Sholat Dhuha Berjamaah',
        'type' => 'sholat_dhuha',
        'event_date' => now()->format('Y-m-d'),
        'target' => 'all_students',
        'created_by' => $this->teacherUser->id,
    ]);

    EventAttendanceRecord::factory()->create([
        'event_id' => $event->id,
        'user_id' => $this->studentUserA->id,
        'status' => 'hadir',
    ]);

    // Student A views own rekap
    $response = $this->actingAs($this->studentUserA)->get(route('event-attendance.rekap'));
    $response->assertStatus(200);
    $response->assertViewHas('student');
    $response->assertViewHas('summary');

    // Student A cannot view Student B's rekap (ignores query params for students)
    $response = $this->actingAs($this->studentUserA)->get(route('event-attendance.rekap', ['student_id' => $this->studentB->id]));
    $response->assertStatus(200);
    // Should still load Student A's data
    $viewData = $response->viewData('student');
    expect($viewData->id)->toBe($this->studentA->id);
});

test('parent can view their child attendance rekap', function () {
    $event = EventAttendanceEvent::factory()->create([
        'name' => 'Sholat Dhuha Berjamaah',
        'type' => 'sholat_dhuha',
        'event_date' => now()->format('Y-m-d'),
        'target' => 'all_students',
        'created_by' => $this->teacherUser->id,
    ]);

    EventAttendanceRecord::factory()->create([
        'event_id' => $event->id,
        'user_id' => $this->studentUserA->id,
        'status' => 'hadir',
    ]);

    // Parent A views their child (Student A)
    $response = $this->actingAs($this->parentUserA)->get(route('event-attendance.rekap', ['student_id' => $this->studentA->id]));
    $response->assertStatus(200);
    $response->assertViewHas('student');
    expect($response->viewData('student')->id)->toBe($this->studentA->id);
});

test('teacher can export event attendance to pdf', function () {
    $event = EventAttendanceEvent::factory()->create([
        'name' => 'Kultum PAI Kelas XII-IPA-1',
        'type' => 'kultum',
        'event_date' => now()->format('Y-m-d'),
        'target' => 'all_students',
        'created_by' => $this->teacherUser->id,
    ]);

    EventAttendanceRecord::factory()->create([
        'event_id' => $event->id,
        'user_id' => $this->studentUserA->id,
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($this->teacherUser)->get(route('event-attendance.export-pdf', $event->id));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});
