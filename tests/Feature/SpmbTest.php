<?php

/** @var TestCase|stdClass $this */

use App\Mail\RegistrationSubmitted;
use App\Mail\StudentAccountCreated;
use App\Models\AcademicYear;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    Mail::fake();
    Storage::fake('public');
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
});

test('public user can submit registration', function () {
    $response = $this->post(route('spmb.store'), [
        'full_name' => 'Calon Siswa Baru',
        'email' => 'calon@example.com',
        'nisn' => '1234567890',
        'previous_school' => 'SMP Maju Jaya',
        'parent_phone' => '08123456789',
        'document_kk' => UploadedFile::fake()->image('kk.jpg'),
        'document_certificate' => UploadedFile::fake()->create('ijazah.pdf'),
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
    ]);

    Mail::assertQueued(StudentAccountCreated::class);
});
