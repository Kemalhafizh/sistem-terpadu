<?php

use App\Models\AcademicYear;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbTrack;
use App\Models\Registration;
use App\Models\Classroom;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->academicYear = AcademicYear::create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-01',
        'end_date' => '2026-12-31',
    ]);

    $this->period = SpmbPeriod::create([
        'academic_year_id' => $this->academicYear->id,
        'name' => 'SPMB 2026/2027',
        'total_quota' => 100,
        'status' => 'open',
    ]);

    $this->wave = SpmbWave::create([
        'spmb_period_id' => $this->period->id,
        'name' => 'Gelombang 1',
        'registration_start' => '2026-07-01',
        'registration_end' => '2026-07-31',
        'announcement_date' => '2026-08-05',
        'quota' => 55,
        'registration_fee' => 150000,
        'status' => 'open',
    ]);

    $this->track = SpmbTrack::create([
        'spmb_period_id' => $this->period->id,
        'name' => 'Jalur Reguler',
        'quota' => 40,
    ]);

    $this->classroom = Classroom::create([
        'name' => 'Kelas X-A',
        'academic_year' => '2026/2027',
        'grade_level' => 'X',
        'major' => 'Umum',
    ]);

    $this->admin = User::factory()->create([
        'name' => 'PPDB Admin',
        'email' => 'ppdbadmin@sista.com',
        'role' => 'admin',
    ]);

    Mail::fake();
});

test('admin can confirm reregistration and convert applicant to student', function () {
    $reg = Registration::create([
        'registration_number' => 'REG20260004',
        'full_name' => 'Dwi Lestari',
        'email' => 'dwi@gmail.com',
        'nisn' => '3082918905',
        'parent_phone' => '081299887755',
        'parent_name' => 'Budi Lestari',
        'previous_school' => 'SMP N 3 Semarang',
        'gender' => 'P',
        'status' => 'accepted',
        'academic_year_id' => $this->academicYear->id,
        'spmb_wave_id' => $this->wave->id,
        'spmb_track_id' => $this->track->id,
        'has_paid_registration' => true,
        'has_reregistered' => false,
    ]);

    // 1. Confirm reregistration
    $response = $this->actingAs($this->admin)->post(route('spmb.admin.reregistration.confirm', $reg));
    $response->assertRedirect();
    
    $reg->refresh();
    expect($reg->has_reregistered)->toBe(true);

    // 2. Perform bulk convert
    $response2 = $this->actingAs($this->admin)->post(route('spmb.admin.bulk-convert', $this->wave));
    $response2->assertRedirect();

    // Assert student user account created
    $expectedEmail = strtolower($reg->registration_number).'@student.sch.id';
    $this->assertDatabaseHas('users', [
        'email' => $expectedEmail,
        'role' => 'student',
    ]);

    // Assert student record created
    $this->assertDatabaseHas('students', [
        'nisn' => $reg->nisn,
    ]);
});
