<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->student1 = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->student2 = Student::factory()->create(['classroom_id' => $this->classroom->id]);
    $this->subject = Subject::factory()->create(['kkm' => 75]);
});

// ── INDEX ──────────────────────────────────────────────────────────────

test('admin can view report cards index', function () {
    $response = $this->actingAs($this->admin)->get(route('report-cards.index'));

    $response->assertStatus(200);
    $response->assertViewIs('report-cards.index');
});

test('admin can filter report cards by classroom', function () {
    $response = $this->actingAs($this->admin)->get(route('report-cards.index', [
        'classroom_id' => $this->classroom->id,
    ]));

    $response->assertStatus(200);
    $response->assertViewHas('students');
});

// ── SYNC (Kalkulasi Rapor) ────────────────────────────────────────────

test('admin can sync report cards and calculate ranking', function () {
    // Setup: buat beberapa nilai
    Grade::create([
        'student_id' => $this->student1->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh',
        'score' => 90,
        'recorded_by' => $this->guru->id,
    ]);

    Grade::create([
        'student_id' => $this->student2->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'uh',
        'score' => 80,
        'recorded_by' => $this->guru->id,
    ]);

    $response = $this->actingAs($this->admin)->post(route('report-cards.sync'), [
        'classroom_id' => $this->classroom->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verifikasi rapor dan ranking terbuat dengan benar
    $reportCard1 = ReportCard::where('student_id', $this->student1->id)->first();
    $reportCard2 = ReportCard::where('student_id', $this->student2->id)->first();

    expect($reportCard1)->not->toBeNull();
    expect($reportCard2)->not->toBeNull();

    // Student1 (skor 90) harus ranking 1
    expect($reportCard1->rank_in_class)->toBe(1);
    // Student2 (skor 80) harus ranking 2
    expect($reportCard2->rank_in_class)->toBe(2);
});

test('sync fails when no active academic year', function () {
    // Nonaktifkan tahun ajaran
    $this->academicYear->update(['is_active' => false]);

    $response = $this->actingAs($this->admin)->post(route('report-cards.sync'), [
        'classroom_id' => $this->classroom->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('sync validates classroom_id is required', function () {
    $response = $this->actingAs($this->admin)->post(route('report-cards.sync'), []);

    $response->assertSessionHasErrors('classroom_id');
});

// ── STORE NOTES ───────────────────────────────────────────────────────

test('admin can store homeroom notes for report cards', function () {
    // Setup: buat report card terlebih dahulu
    $reportCard = ReportCard::create([
        'student_id' => $this->student1->id,
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom->id,
        'total_score' => 90,
        'average_score' => 90,
        'rank_in_class' => 1,
    ]);

    $response = $this->actingAs($this->admin)->post(route('report-cards.store-notes'), [
        'report_cards' => [
            $reportCard->id => [
                'homeroom_notes' => 'Siswa berprestasi dan rajin.',
            ],
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $reportCard->refresh();
    expect($reportCard->homeroom_notes)->toBe('Siswa berprestasi dan rajin.');
});

// ── PDF PRINT ─────────────────────────────────────────────────────────

test('admin can print report card PDF', function () {
    // Setup minimal data
    ReportCard::create([
        'student_id' => $this->student1->id,
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom->id,
        'total_score' => 90,
        'average_score' => 90,
        'rank_in_class' => 1,
    ]);

    $response = $this->actingAs($this->admin)->get(route('report-cards.pdf', $this->student1));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('student can only print their own report card', function () {
    $otherStudent = Student::factory()->create();

    $studentUser = $this->student1->user;

    $response = $this->actingAs($studentUser)->get(route('report-cards.pdf', $otherStudent));

    $response->assertStatus(403);
});
