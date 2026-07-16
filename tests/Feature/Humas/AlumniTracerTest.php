<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\AlumniData;
use App\Models\Student;
use App\Models\User;
use App\Models\TracerResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

beforeEach(function () {
    // 1. Setup Roles
    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Agus Admin']);
    $this->humas = User::factory()->create(['role' => 'waka_humas', 'name' => 'Hendri Humas']);
    $this->activeStudentUser = User::factory()->create(['role' => 'student', 'name' => 'Ahmad Siswa']);
    
    $this->alumniUser = User::factory()->create(['role' => 'student', 'name' => 'Alumni Siswa']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    // 2. Setup Academic Data
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true, 'year' => '2025/2026']);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);

    // Active Student
    $this->activeStudent = Student::factory()->create([
        'user_id' => $this->activeStudentUser->id,
        'classroom_id' => $this->classroom->id,
        'status' => 'active'
    ]);

    // Alumni Student
    $this->alumniStudent = Student::factory()->create([
        'user_id' => $this->alumniUser->id,
        'classroom_id' => $this->classroom->id,
        'status' => 'alumni'
    ]);

    // Create AlumniData for the alumni student
    $this->alumniData = AlumniData::create([
        'student_id' => $this->alumniStudent->id,
        'graduation_year' => 2025,
        'current_activity' => 'other',
        'email' => $this->alumniUser->email,
        'phone_number' => '08123456789'
    ]);
});

test('waka humas or admin can graduate students in bulk', function () {
    $this->actingAs($this->humas);

    $response = $this->post(route('alumni.bulk-graduate.store'), [
        'student_ids' => [$this->activeStudent->id],
        'graduation_year' => 2026
    ]);

    $response->assertRedirect(route('alumni.index'));
    $this->assertDatabaseHas('students', [
        'id' => $this->activeStudent->id,
        'status' => 'alumni'
    ]);
    $this->assertDatabaseHas('alumni_data', [
        'student_id' => $this->activeStudent->id,
        'graduation_year' => 2026
    ]);
});

test('alumni can fill out tracer study questionnaire and edit it later', function () {
    $this->actingAs($this->alumniUser);

    // 1. Submit Form
    $response = $this->post(route('alumni.tracer-study.submit'), [
        'current_activity' => 'work',
        'employer_name' => 'PT Sultan Agung Jaya',
        'job_title' => 'Software Engineer',
        'entry_year' => 2025,
        'salary_range' => '3M - 5M',
        'relevance_score' => 5,
        'satisfaction_score' => 4,
        'suggestions' => 'Tingkatkan praktikum komputer.'
    ]);

    $response->assertRedirect(route('alumni.tracer-study.form'));
    $this->assertDatabaseHas('tracer_responses', [
        'alumni_data_id' => $this->alumniData->id,
        'job_title' => 'Software Engineer',
        'employer_name' => 'PT Sultan Agung Jaya',
        'relevance_score' => 5
    ]);

    // Retrieve Response for editing
    $tracerResponse = TracerResponse::where('alumni_data_id', $this->alumniData->id)->first();

    // 2. Edit/Update Form
    $editResponse = $this->put(route('alumni.tracer-study.update', $tracerResponse->uuid), [
        'current_activity' => 'work',
        'employer_name' => 'PT Sultan Agung Jaya',
        'job_title' => 'Senior Developer',
        'entry_year' => 2025,
        'salary_range' => '5M - 10M',
        'relevance_score' => 5,
        'satisfaction_score' => 5,
        'suggestions' => 'Saran diubah.'
    ]);

    $editResponse->assertRedirect(route('alumni.tracer-study.form'));
    $this->assertDatabaseHas('tracer_responses', [
        'id' => $tracerResponse->id,
        'job_title' => 'Senior Developer',
        'salary_range' => '5M - 10M',
        'suggestions' => 'Saran diubah.'
    ]);
});

test('waka humas can access analytics, comparison, and invitation pages', function () {
    $this->actingAs($this->humas);

    $this->get(route('alumni.dashboard'))->assertStatus(200);
    $this->get(route('alumni.tracer-study.analytics'))->assertStatus(200);
    $this->get(route('alumni.tracer-study.comparison'))->assertStatus(200);
    $this->get(route('alumni.tracer-study.invitation'))->assertStatus(200);
});

test('unauthorized roles are blocked from alumni administration', function () {
    // Parent user should be blocked from admin dashboards
    $this->actingAs($this->parentUser);
    $this->get(route('alumni.dashboard'))->assertStatus(403);
    $this->get(route('alumni.bulk-graduate'))->assertStatus(403);
    $this->get(route('alumni.tracer-study.analytics'))->assertStatus(403);

    // Active student user should be blocked
    $this->actingAs($this->activeStudentUser);
    $this->get(route('alumni.dashboard'))->assertStatus(403);
});

test('export endpoints return success', function () {
    $this->actingAs($this->humas);

    // 1. Export Excel CSV
    $this->get(route('alumni.export.excel'))->assertStatus(200);
    $this->get(route('alumni.tracer-study.export.excel'))->assertStatus(200);

    // 2. Export PDF Book
    $this->get(route('alumni.export.pdf', 2025))->assertStatus(200);
});
