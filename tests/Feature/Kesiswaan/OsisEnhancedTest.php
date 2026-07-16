<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\OsisMember;
use App\Models\OsisWorkProgram;
use App\Models\OsisElection;
use App\Models\OsisCandidate;
use App\Models\OsisVote;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

beforeEach(function () {
    // 1. Setup Roles
    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Kamil Admin']);
    $this->waka = User::factory()->create(['role' => 'waka_kesiswaan', 'name' => 'Woko Waka']);
    $this->studentUser1 = User::factory()->create(['role' => 'student', 'name' => 'Siswa Satu']);
    $this->studentUser2 = User::factory()->create(['role' => 'student', 'name' => 'Siswa Dua']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2025/2026']);

    $this->student1 = Student::factory()->create([
        'user_id' => $this->studentUser1->id,
        'status' => 'active'
    ]);
    
    $this->student2 = Student::factory()->create([
        'user_id' => $this->studentUser2->id,
        'status' => 'active'
    ]);
});

test('admin or waka can manage OSIS members and work programs', function () {
    $this->actingAs($this->waka);

    // 1. Add Member
    $memberResponse = $this->post(route('osis.members.store'), [
        'student_id' => $this->student1->id,
        'position' => 'Ketua Umum',
    ]);
    $memberResponse->assertRedirect();
    $this->assertDatabaseHas('osis_members', [
        'student_id' => $this->student1->id,
        'position' => 'Ketua Umum',
        'academic_year_id' => $this->academicYear->id,
    ]);

    $member = OsisMember::first();

    // 2. Update Member
    $updateMemberResponse = $this->put(route('osis.members.update', $member->id), [
        'position' => 'Ketua Umum Baru',
    ]);
    $updateMemberResponse->assertRedirect();
    $this->assertDatabaseHas('osis_members', [
        'id' => $member->id,
        'position' => 'Ketua Umum Baru',
    ]);

    // 3. Add Work Program
    $prokerResponse = $this->post(route('osis.work-programs.store'), [
        'title' => 'Bakti Sosial Ramadhan',
        'sekbid' => 'Sekbid 1: Keimanan & Ketaqwaan YME',
        'description' => 'Membagikan sembako.',
        'budget_plan' => 2000000,
        'planned_date' => '2026-04-12',
    ]);
    $prokerResponse->assertRedirect();
    $this->assertDatabaseHas('osis_work_programs', [
        'title' => 'Bakti Sosial Ramadhan',
        'budget_plan' => 2000000.00,
    ]);
});

test('admin can manage elections, and students can vote securely once', function () {
    $this->actingAs($this->waka);

    // 1. Create Election
    $electResponse = $this->post(route('osis.elections.store'), [
        'title' => 'Pemilihan Ketua OSIS 2026',
        'voting_start' => '2026-05-01',
        'voting_end' => '2026-05-02',
    ]);
    $electResponse->assertRedirect();
    $this->assertDatabaseHas('osis_elections', [
        'title' => 'Pemilihan Ketua OSIS 2026',
    ]);

    $election = OsisElection::first();

    // Update status to active manually for testing cast vote
    $election->update(['status' => 'active']);

    // 2. Create Candidate
    $candResponse = $this->post(route('osis.elections.candidates.store', $election->id), [
        'student_id' => $this->student1->id,
        'candidate_number' => 1,
        'vision' => 'Visi mantap',
        'mission' => 'Misi sukses',
    ]);
    $candResponse->assertRedirect();
    $this->assertDatabaseHas('osis_candidates', [
        'election_id' => $election->id,
        'student_id' => $this->student1->id,
        'candidate_number' => 1,
    ]);

    $candidate = OsisCandidate::first();

    // 3. Student 1 votes successfully
    $this->actingAs($this->studentUser1);
    $voteResponse = $this->post(route('osis.elections.vote', $election->id), [
        'candidate_id' => $candidate->id,
    ]);
    $voteResponse->assertRedirect();
    $this->assertDatabaseHas('osis_votes', [
        'election_id' => $election->id,
        'voter_id' => $this->student1->id,
        'candidate_id' => $candidate->id,
    ]);

    $this->assertEquals(1, $candidate->fresh()->vote_count);

    // 4. Student 1 votes again and gets blocked
    $doubleVoteResponse = $this->post(route('osis.elections.vote', $election->id), [
        'candidate_id' => $candidate->id,
    ]);
    $doubleVoteResponse->assertRedirect();
    $this->assertEquals(1, $candidate->fresh()->vote_count);
});

test('unauthorized roles are blocked from administrative panels', function () {
    $this->actingAs($this->studentUser1);

    // Student should be blocked from managing members page
    $this->get(route('osis.members'))->assertStatus(403);
});
