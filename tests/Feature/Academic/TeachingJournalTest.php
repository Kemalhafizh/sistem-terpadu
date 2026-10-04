<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    // 1. Setup Users
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru']);
    $this->guruLain = User::factory()->create(['role' => 'guru', 'name' => 'Lani Guru']);
    $this->wakakur = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Joko Wakakur']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Hadi Kepsek']);
    $this->student = User::factory()->create(['role' => 'student']);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2026/2027']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);
    $this->subject = Subject::factory()->create(['name' => 'Matematika']);

    // Associate subject with teacher
    $this->guru->subjects()->attach($this->subject->id);
    $this->guruLain->subjects()->attach($this->subject->id);

    // Budi teaches Matematika in this class this year; Lani does not.
    // A journal may only be filed for a lesson on the teacher's schedule.
    Schedule::create([
        'academic_year_id' => $this->academicYear->id, 'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id, 'teacher_id' => $this->guru->id,
        'day' => 'Senin', 'session_start' => 1, 'session_end' => 2,
    ]);
});

test('teachers can view index page and only see their own journals, while coordinators see all', function () {
    // Create journal for guru
    $journalBudi = TeachingJournal::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => now()->format('Y-m-d'),
        'topic' => 'Aljabar Dasar',
        'status' => 'draft',
    ]);

    // Create journal for guruLain
    $journalLani = TeachingJournal::create([
        'user_id' => $this->guruLain->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => now()->format('Y-m-d'),
        'topic' => 'Geometri Dasar',
        'status' => 'draft',
    ]);

    // 1. As Budi Guru (should only see Budi's journals)
    $response = $this->actingAs($this->guru)->get(route('teaching-journals.index'));
    $response->assertStatus(200);
    $response->assertSee('Aljabar Dasar');
    $response->assertDontSee('Geometri Dasar');

    // 2. As Joko Wakakur (should see both)
    $response = $this->actingAs($this->wakakur)->get(route('teaching-journals.index'));
    $response->assertStatus(200);
    $response->assertSee('Aljabar Dasar');
    $response->assertSee('Geometri Dasar');

    // 3. As Student (should be forbidden)
    $response = $this->actingAs($this->student)->get(route('teaching-journals.index'));
    $response->assertStatus(403);
});

test('teacher can create a draft journal and a submitted journal', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('document.pdf', 500);

    // 1. Save as Draft
    $response = $this->actingAs($this->guru)->post(route('teaching-journals.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teaching_date' => now()->format('Y-m-d'),
        'jam_ke' => 3,
        'topic' => 'Trigonometri Lanjutan',
        'learning_activity' => 'Siswa berdiskusi kelompok menyelesaikan soal pembuktian rumus.',
        'learning_method' => 'Diskusi Kelompok',
        'students_present' => 32,
        'students_absent' => 2,
        'obstacles' => 'Ada 1 kelompok yang proyektornya bermasalah.',
        'notes' => 'Tugas mandiri halaman 50.',
        'attachment' => $file,
        'action' => 'draft',
    ]);

    $response->assertRedirect(route('teaching-journals.index'));
    $this->assertDatabaseHas('teaching_journals', [
        'user_id' => $this->guru->id,
        'topic' => 'Trigonometri Lanjutan',
        'status' => 'draft',
    ]);

    // 2. Save and Submit directly
    $response2 = $this->actingAs($this->guru)->post(route('teaching-journals.store'), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teaching_date' => now()->format('Y-m-d'),
        'jam_ke' => 4,
        'topic' => 'Kalkulus Integral',
        'learning_activity' => 'Pembahasan latihan soal integral parsial.',
        'learning_method' => 'Tanya Jawab',
        'students_present' => 34,
        'students_absent' => 0,
        'action' => 'submit',
    ]);

    $response2->assertRedirect(route('teaching-journals.index'));
    $this->assertDatabaseHas('teaching_journals', [
        'user_id' => $this->guru->id,
        'topic' => 'Kalkulus Integral',
        'status' => 'submitted',
    ]);
});

test('submitted or reviewed journals are locked and cannot be updated or deleted by teachers', function () {
    // Create a submitted journal
    $journal = TeachingJournal::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => now()->format('Y-m-d'),
        'topic' => 'Suku Banyak / Polinomial',
        'status' => 'submitted',
    ]);

    // Try to edit page (should throw 403 authorization error via policy)
    $responseEdit = $this->actingAs($this->guru)->get(route('teaching-journals.edit', $journal->uuid));
    $responseEdit->assertStatus(403);

    // Try to update via put request (should throw 403 authorization error via policy)
    $responseUpdate = $this->actingAs($this->guru)->put(route('teaching-journals.update', $journal->uuid), [
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teaching_date' => now()->format('Y-m-d'),
        'topic' => 'Suku Banyak / Polinomial Baru',
        'action' => 'submit',
    ]);
    $responseUpdate->assertStatus(403);

    // Try to delete (should throw 403 authorization error via policy)
    $responseDelete = $this->actingAs($this->guru)->delete(route('teaching-journals.destroy', $journal->uuid));
    $responseDelete->assertStatus(403);
});

test('schedule auto-suggest correctly loads today schedule', function () {
    // Determine today's Indonesian name
    $daysMap = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu',
    ];
    $todayIndo = $daysMap[now()->format('l')] ?? 'Senin';

    // Create schedule for Budi Guru today
    $schedule = Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
        'teacher_id' => $this->guru->id,
        'day' => $todayIndo,
        'session_start' => 2,
        'session_end' => 4,
    ]);

    $response = $this->actingAs($this->guru)->get(route('teaching-journals.create'));
    $response->assertStatus(200);
    // Should see suggested schedule card in Sidebar
    $response->assertSee('Sesi Ke-2 s/d 4');
    $response->assertSee('Matematika');
});

test('coordinators can review, approve, or reject (return to draft) submitted journals', function () {
    // Create a submitted journal
    $journal = TeachingJournal::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => now()->format('Y-m-d'),
        'topic' => 'Statistika Deskriptif',
        'status' => 'submitted',
    ]);

    // 1. Approve (set status to reviewed)
    $responseApprove = $this->actingAs($this->wakakur)->post(route('teaching-journals.review', $journal->uuid), [
        'reviewer_notes' => 'Laporan administrasi lengkap dan sesuai jadwal.',
        'status' => 'reviewed',
    ]);

    $responseApprove->assertRedirect(route('teaching-journals.show', $journal->uuid));
    $responseApprove->assertSessionHas('success', 'Jurnal mengajar berhasil ditinjau dan disetujui.');

    $this->assertDatabaseHas('teaching_journals', [
        'id' => $journal->id,
        'status' => 'reviewed',
        'reviewer_notes' => 'Laporan administrasi lengkap dan sesuai jadwal.',
        'reviewed_by' => $this->wakakur->id,
    ]);

    // 2. Reject / Return to draft (let's re-submit and test reject)
    $journal->update(['status' => 'submitted']);

    $responseReject = $this->actingAs($this->kepsek)->post(route('teaching-journals.review', $journal->uuid), [
        'reviewer_notes' => 'Aktivitas belajar kurang detail, mohon lengkapi.',
        'status' => 'draft',
    ]);

    $responseReject->assertRedirect(route('teaching-journals.show', $journal->uuid));
    $responseReject->assertSessionHas('success', 'Jurnal mengajar berhasil ditinjau dan dikembalikan untuk direvisi.');

    $this->assertDatabaseHas('teaching_journals', [
        'id' => $journal->id,
        'status' => 'draft',
        'reviewer_notes' => 'Aktivitas belajar kurang detail, mohon lengkapi.',
        'reviewed_by' => $this->kepsek->id,
    ]);
});

test('coordinators can export monthly PDF rekap reports of reviewed journals', function () {
    // Create a reviewed journal for this month
    $journalReviewed = TeachingJournal::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => now()->format('Y-m-d'),
        'topic' => 'Vektor Dan Matriks',
        'status' => 'reviewed',
    ]);

    // Create a draft journal for this month (should NOT be included in PDF export)
    $journalDraft = TeachingJournal::create([
        'user_id' => $this->guru->id,
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => now()->format('Y-m-d'),
        'topic' => 'Persamaan Vektor',
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->wakakur)->get(route('teaching-journals.export-pdf', [
        'month' => now()->format('n'),
        'year' => now()->format('Y'),
        'teacher_id' => $this->guru->id,
    ]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    $month = now()->format('n');
    $year = now()->format('Y');
    $response->assertHeader('content-disposition', "attachment; filename=rekap_jurnal_mengajar_{$month}_{$year}.pdf");
});

test('guru tidak bisa mengisi atau memindahkan jurnal ke kelas yang tidak ia ajar', function () {
    $payload = [
        'classroom_id' => $this->classroom->id, 'subject_id' => $this->subject->id,
        'teaching_date' => now()->format('Y-m-d'), 'topic' => 'Bukan kelas saya', 'action' => 'draft',
    ];

    // Lani has no schedule in this class: she could file journals for it before.
    $this->actingAs($this->guruLain)->post(route('teaching-journals.store'), $payload)->assertForbidden();
    expect(TeachingJournal::count())->toBe(0);

    // Budi may file it, but not move it to a class he does not teach.
    $this->actingAs($this->guru)->post(route('teaching-journals.store'), $payload)->assertRedirect();
    $journal = TeachingJournal::sole();
    $otherClass = Classroom::factory()->create();

    $this->put(route('teaching-journals.update', $journal->uuid), ['classroom_id' => $otherClass->id] + $payload)->assertForbidden();
    expect($journal->fresh()->classroom_id)->toBe($this->classroom->id);
});
