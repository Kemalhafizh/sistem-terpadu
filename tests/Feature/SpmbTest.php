<?php

/** @var TestCase|stdClass $this */

use App\Mail\RegistrationSubmitted;
use App\Mail\StudentAccountCreated;
use App\Models\AcademicYear;
use App\Models\PaymentCategory;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
    $this->academicYear = AcademicYear::factory()->create([
        'is_active' => true,
        'year' => '2026/2027',
        'semester' => 'Ganjil',
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
        'registration_start' => now()->subDays(5)->format('Y-m-d'),
        'registration_end' => now()->addDays(5)->format('Y-m-d'),
        'quota' => 50,
        'registration_fee' => 150000,
        'status' => 'open',
    ]);

    $this->track = SpmbTrack::create([
        'spmb_period_id' => $this->period->id,
        'name' => 'Reguler',
        'quota' => 30,
        'scoring_weights' => [
            'rapor' => 40,
            'tes' => 30,
            'prestasi' => 15,
            'tahfidz' => 15,
        ],
        'requirements' => 'Persyaratan Reguler',
    ]);
});

test('public user can submit registration', function () {
    $response = $this->post(route('spmb.store'), [
        'spmb_wave_id' => $this->wave->id,
        'spmb_track_id' => $this->track->id,
        'full_name' => 'Calon Siswa Baru',
        'email' => 'calon@example.com',
        'nisn' => '1234567890',
        'previous_school' => 'SMP Maju Jaya',
        'parent_phone' => '08123456789',
        'gender' => 'L',
        'birth_date' => '2010-05-15',
        'birth_place' => 'Semarang',
        'address' => 'Jl. Pemuda No. 100, Semarang',
        'parent_name' => 'Budi Wijaya',
        'document_kk' => UploadedFile::fake()->create('kk.pdf', 500, 'application/pdf'),
        'document_certificate' => UploadedFile::fake()->create('ijazah.pdf', 500, 'application/pdf'),
        'photo' => UploadedFile::fake()->image('photo.jpg'),
        'rapor_scores' => [
            'matematika' => 85,
            'ipa' => 80,
            'ips' => 80,
            'indonesia' => 90,
            'inggris' => 85,
        ],
    ]);

    $response->assertRedirect(route('spmb.success'));
    $this->assertDatabaseHas('registrations', [
        'full_name' => 'Calon Siswa Baru',
        'email' => 'calon@example.com',
        'nisn' => '1234567890',
        'status' => 'pending',
    ]);
    Mail::assertQueued(RegistrationSubmitted::class);
});

test('admin can convert registration to student', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    // The admission fee comes from the category Keuangan set up; without one no bill is issued.
    $category = PaymentCategory::create(['name' => 'Uang Pangkal', 'default_amount' => 6000000, 'is_monthly' => false]);
    $registration = Registration::factory()->create([
        'email' => 'calon@example.com',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->post(route('spmb.admin.status', $registration), [
        'status' => 'accepted',
    ]);

    $response->assertSessionHas('success');

    $registration->refresh();
    $this->assertEquals('accepted', $registration->status);
    $this->assertNotNull($registration->user_id);

    $this->assertDatabaseHas('users', [
        'id' => $registration->user_id,
        'role' => 'student',
        'must_change_password' => true,
    ]);

    $this->assertDatabaseHas('students', [
        'user_id' => $registration->user_id,
    ]);

    $this->assertDatabaseHas('billings', [
        'academic_year_id' => $this->academicYear->id,
        'payment_category_id' => $category->id,
        'amount' => 6000000,
    ]);

    Mail::assertQueued(StudentAccountCreated::class);
});
