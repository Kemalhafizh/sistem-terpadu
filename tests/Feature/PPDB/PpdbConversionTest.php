<?php

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\PaymentCategory;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;
use App\Models\Student;
use App\Models\User;
use App\Services\RegistrationService;
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

function acceptedApplicant(object $test, array $attributes = []): Registration
{
    return Registration::create([
        'registration_number' => 'REG2026'.fake()->unique()->numerify('####'),
        'full_name' => 'Aisyah Putri',
        'email' => fake()->unique()->safeEmail(),
        'nisn' => '3082918906',
        'parent_phone' => '081299887755',
        'parent_name' => 'Ahmad',
        'previous_school' => 'SMP N 1 Demak',
        'gender' => 'P',
        'birth_date' => '2011-02-14',
        'birth_place' => 'Demak',
        'address' => 'Jl. Kauman 5, Demak',
        'status' => 'accepted',
        'academic_year_id' => $test->academicYear->id,
        'spmb_wave_id' => $test->wave->id,
        'spmb_track_id' => $test->track->id,
        'has_paid_registration' => true,
        'has_reregistered' => true,
        ...$attributes,
    ]);
}

test('siswa hasil SPMB membawa data pendaftarannya, bukan nilai karangan', function () {
    $reg = acceptedApplicant($this);

    $this->actingAs($this->admin)->post(route('spmb.admin.bulk-convert', $this->wave))->assertRedirect();

    // Used to be gender "L" for everyone, address "Belum diisi", and no birth date.
    expect(Student::where('user_id', $reg->fresh()->user_id)->sole())
        ->gender->toBe('P')
        ->birth_place->toBe('Demak')
        ->address->toBe('Jl. Kauman 5, Demak')
        ->and((string) Student::where('user_id', $reg->fresh()->user_id)->sole()->birth_date)->toStartWith('2011-02-14');
});

test('pendaftar tanpa NISN tidak dijadikan siswa dengan NISN karangan, dan admin diberi tahu', function () {
    $reg = acceptedApplicant($this, ['nisn' => null, 'full_name' => 'Nadia Tanpa NISN']);
    $ok = acceptedApplicant($this, ['nisn' => '3082918909']);

    $this->actingAs($this->admin)->post(route('spmb.admin.bulk-convert', $this->wave))
        ->assertRedirect()
        ->assertSessionHas('success', '1 calon siswa dijadikan siswa aktif.')
        ->assertSessionHas('warning', fn (string $message) => str_contains($message, 'NISN Nadia Tanpa NISN belum diisi'));

    // It used to get an invented "NSN<timestamp>".
    expect($reg->fresh()->user_id)->toBeNull()
        ->and($ok->fresh()->user_id)->not->toBeNull()
        ->and(Student::where('nisn', 'like', 'NSN%')->exists())->toBeFalse();
});

test('tagihan uang pangkal mengikuti kategori Keuangan, dan tidak dibuat bila belum diatur', function () {
    $first = acceptedApplicant($this);
    $this->actingAs($this->admin)->post(route('spmb.admin.bulk-convert', $this->wave));

    // No category: no bill, and no invented Rp 5.000.000 category either.
    expect(Billing::count())->toBe(0)
        ->and(PaymentCategory::count())->toBe(0);

    $result = app(RegistrationService::class)->convertToStudent(acceptedApplicant($this, ['nisn' => '3082918907']));
    expect($result['billed'])->toBeFalse()
        ->and($result['message'])->toContain('Tagihan uang pangkal belum dibuat');

    // The seeded name "Uang Pangkal" used to be missed ("Uang Pangkal / Pendaftaran" was looked up).
    $category = PaymentCategory::create(['name' => 'Uang Pangkal', 'default_amount' => 7500000, 'is_monthly' => false]);
    $third = acceptedApplicant($this, ['nisn' => '3082918908']);
    app(RegistrationService::class)->convertToStudent($third);

    $student = Student::where('user_id', $third->fresh()->user_id)->sole();
    expect(Billing::sole())->student_id->toBe($student->id)->payment_category_id->toBe($category->id)
        ->and((float) Billing::sole()->amount)->toBe(7500000.0)
        ->and($first->fresh()->user_id)->not->toBeNull();
});
