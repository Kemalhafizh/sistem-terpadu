<?php

use App\Models\AcademicYear;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

    Mail::fake();
});

test('public welcome page is accessible', function () {
    $response = $this->get(route('spmb.welcome'));
    $response->assertStatus(200);
});

test('public registration page is accessible', function () {
    $response = $this->get(route('spmb.create'));
    $response->assertStatus(200);
});

test('applicant can submit registration successfully', function () {
    Storage::fake('public');
    $kk = UploadedFile::fake()->create('kk.pdf', 500);
    $cert = UploadedFile::fake()->create('certificate.pdf', 500);
    $photo = UploadedFile::fake()->image('photo.jpg');

    $response = $this->post(route('spmb.store'), [
        'full_name' => 'Farhan Azhar',
        'email' => 'farhan.azhar@gmail.com',
        'nisn' => '3082918273',
        'previous_school' => 'SMP N 1 Semarang',
        'parent_phone' => '081299887766',
        'gender' => 'L',
        'birth_date' => '2010-05-15',
        'birth_place' => 'Semarang',
        'address' => 'Jl. Ketileng No. 12',
        'parent_name' => 'Budi Azhar',
        'spmb_wave_id' => $this->wave->id,
        'spmb_track_id' => $this->track->id,
        'document_kk' => $kk,
        'document_certificate' => $cert,
        'photo' => $photo,
        'rapor_scores' => ['ipa' => 85, 'matematika' => 90],
    ]);

    $response->assertRedirect(route('spmb.success'));
    $this->assertDatabaseHas('registrations', [
        'full_name' => 'Farhan Azhar',
        'nisn' => '3082918273',
    ]);
});

test('status check page displays status of applicant', function () {
    $reg = Registration::create([
        'registration_number' => 'REG20260001',
        'full_name' => 'Novi Indri',
        'email' => 'novi@gmail.com',
        'nisn' => '3082918902',
        'previous_school' => 'SMP N 3 Semarang',
        'parent_phone' => '081299887755',
        'gender' => 'P',
        'birth_date' => '2010-08-20',
        'birth_place' => 'Semarang',
        'address' => 'Jl. Kedungmundu No. 1',
        'parent_name' => 'Joko',
        'status' => 'draft',
        'academic_year_id' => $this->academicYear->id,
        'spmb_wave_id' => $this->wave->id,
        'spmb_track_id' => $this->track->id,
    ]);

    $response = $this->get(route('spmb.status', ['search' => 'REG20260001']));
    $response->assertStatus(200);
});

test('public announcement page renders correctly', function () {
    $response = $this->get(route('spmb.announcement'));
    $response->assertStatus(200);
});
