<?php

use App\Models\AlumniData;
use App\Models\Student;
use App\Models\TracerResponse;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    // 1. Setup Student that is Alumni
    $this->alumniUser = User::factory()->create(['role' => 'student']);
    $this->alumniStudent = Student::create([
        'user_id' => $this->alumniUser->id,
        'nis' => '10001',
        'nisn' => '00112233',
        'gender' => 'male',
        'birth_date' => '2008-01-01',
        'address' => 'Jl. Kebon Agung',
        'status' => 'alumni',
    ]);
    $this->alumniData = AlumniData::create([
        'student_id' => $this->alumniStudent->id,
        'graduation_year' => 2025,
        'current_activity' => 'other',
    ]);

    // 2. Setup Active Student (non-alumni)
    $this->activeUser = User::factory()->create(['role' => 'student']);
    $this->activeStudent = Student::create([
        'user_id' => $this->activeUser->id,
        'nis' => '10002',
        'nisn' => '00112244',
        'gender' => 'female',
        'birth_date' => '2009-02-02',
        'address' => 'Jl. Kaligawe',
        'status' => 'active',
    ]);

    // 3. Setup Humas Staff
    $this->humasUser = User::factory()->create(['role' => 'waka_humas']);
});

test('alumni can access tracer study form and submit it successfully', function () {
    $response = $this->actingAs($this->alumniUser)->get(route('alumni.tracer-study.form'));
    $response->assertStatus(200);

    $submitData = [
        'current_activity' => 'work',
        'institution_name' => 'PT Google Indonesia',
        'job_title' => 'Software Engineer',
        'salary_range' => '5M - 10M',
        'relevance_score' => 5,
        'satisfaction_score' => 4,
        'suggestions' => 'Tingkatkan materi pemrograman dasar.',
    ];

    $response = $this->actingAs($this->alumniUser)->post(route('alumni.tracer-study.submit'), $submitData);
    $response->assertRedirect(route('alumni.tracer-study.form'));

    // Check databases
    $this->assertDatabaseHas('alumni_data', [
        'id' => $this->alumniData->id,
        'current_activity' => 'work',
        'institution_name' => 'PT Google Indonesia',
    ]);

    $this->assertDatabaseHas('tracer_responses', [
        'alumni_data_id' => $this->alumniData->id,
        'job_title' => 'Software Engineer',
        'salary_range' => '5M - 10M',
        'relevance_score' => 5,
        'satisfaction_score' => 4,
        'suggestions' => 'Tingkatkan materi pemrograman dasar.',
    ]);
});

test('active student cannot access or submit tracer study form', function () {
    // 1. Try to access form
    $response = $this->actingAs($this->activeUser)->get(route('alumni.tracer-study.form'));
    $response->assertStatus(403);

    // 2. Try to submit form
    $submitData = [
        'current_activity' => 'study',
        'institution_name' => 'Universitas Diponegoro',
        'study_major' => 'Teknik Elektro',
        'relevance_score' => 4,
        'satisfaction_score' => 5,
    ];

    $response = $this->actingAs($this->activeUser)->post(route('alumni.tracer-study.submit'), $submitData);
    $response->assertStatus(403);
});

test('humas staff can see visual analytics summary', function () {
    // Fill in a few responses
    TracerResponse::create([
        'alumni_data_id' => $this->alumniData->id,
        'job_title' => 'Engineer',
        'salary_range' => '5M - 10M',
        'relevance_score' => 5,
        'satisfaction_score' => 4,
    ]);

    $response = $this->actingAs($this->humasUser)->get(route('alumni.tracer-study.analytics'));
    $response->assertStatus(200);
    $response->assertSee($this->alumniStudent->user->name);
});
