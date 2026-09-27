<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\OsisCandidate;
use App\Models\OsisElection;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->studentUser = User::factory()->create([
        'role' => 'student',
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'name' => 'Kelas X-A',
        'academic_year' => $this->academicYear->year,
        'grade_level' => 'X',
        'major' => 'Umum',
    ]);

    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    $this->candidateStudentUser = User::factory()->create([
        'role' => 'student',
    ]);

    $this->candidateStudent = Student::factory()->create([
        'user_id' => $this->candidateStudentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);
});

test('admin can create election', function () {
    $response = $this->actingAs($this->admin)->post(route('osis.elections.store'), [
        'title' => 'Pemilihan Ketua OSIS 2026/2027',
        'voting_start' => '2026-07-01',
        'voting_end' => '2026-07-02',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('osis_elections', [
        'title' => 'Pemilihan Ketua OSIS 2026/2027',
    ]);
});

test('admin can add candidate to election', function () {
    $election = OsisElection::create([
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Pemilu OSIS 2026',
        'voting_start' => '2026-07-01',
        'voting_end' => '2026-07-02',
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->admin)->post(route('osis.elections.candidates.store', $election), [
        'student_id' => $this->candidateStudent->id,
        'candidate_number' => 1,
        'vision' => 'Menjadikan sekolah aktif dan inovatif.',
        'mission' => 'Menyelenggarakan kegiatan literasi digital.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('osis_candidates', [
        'election_id' => $election->id,
        'student_id' => $this->candidateStudent->id,
        'candidate_number' => 1,
    ]);
});

test('student can cast vote in active election', function () {
    $election = OsisElection::create([
        'academic_year_id' => $this->academicYear->id,
        'title' => 'Pemilu OSIS 2026',
        'voting_start' => '2026-07-01',
        'voting_end' => '2026-07-02',
        'status' => 'active',
    ]);

    $candidate = OsisCandidate::create([
        'election_id' => $election->id,
        'student_id' => $this->candidateStudent->id,
        'candidate_number' => 1,
        'vision' => 'Visi',
        'mission' => 'Misi',
        'vote_count' => 0,
    ]);

    $response = $this->actingAs($this->studentUser)->post(route('osis.elections.vote', $election), [
        'candidate_id' => $candidate->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('osis_votes', [
        'election_id' => $election->id,
        'voter_id' => $this->student->id,
        'candidate_id' => $candidate->id,
    ]);

    $candidate->refresh();
    expect($candidate->vote_count)->toBe(1);
});
