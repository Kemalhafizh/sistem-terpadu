<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Cache::flush();
    Mail::fake();
    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);
});

test('multi-identifier login works on web and api v1 for email, phone, nis, nisn, and employee numbers', function () {
    $homeroomTeacher = User::factory()->create([
        'name' => 'Pak Hendra',
        'email' => null,
        'phone_number' => '081234567890',
        'password' => Hash::make('Password123!'),
        'role' => 'guru',
        'account_type' => 'guru',
        'must_change_password' => false,
    ]);

    Employee::create([
        'user_id' => $homeroomTeacher->id,
        'employee_number' => 'NIY-202699',
        'nuptk' => '1234567890123456',
        'position' => 'Guru Matematika',
        'employment_status' => 'gty',
    ]);

    $classroom = Classroom::factory()->create([
        'name' => 'X-1',
        'academic_year' => '2026/2027',
        'teacher_id' => $homeroomTeacher->id,
    ]);

    $parentUser = User::factory()->create([
        'name' => 'Ibu Rina',
        'email' => null,
        'phone_number' => '085712345678',
        'password' => Hash::make('ParentPass1!'),
        'role' => 'parent',
        'account_type' => 'orang_tua',
        'must_change_password' => false,
    ]);

    $studentUser = User::factory()->create([
        'name' => 'Ahmad Zaki',
        'email' => 'zaki@student.sulaone.test',
        'phone_number' => null,
        'password' => Hash::make('StudentPass1!'),
        'role' => 'student',
        'account_type' => 'siswa',
        'must_change_password' => false,
    ]);

    Student::factory()->create([
        'user_id' => $studentUser->id,
        'parent_id' => $parentUser->id,
        'classroom_id' => $classroom->id,
        'nis' => '260001',
        'nisn' => '0011223344',
    ]);

    // 1. Web login via NISN
    $this->post('/login', [
        'email' => '0011223344',
        'password' => 'StudentPass1!',
    ])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($studentUser);
    $this->post('/logout');

    // 2. Web login via NIS
    $this->post('/login', [
        'email' => '260001',
        'password' => 'StudentPass1!',
    ])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($studentUser);
    $this->post('/logout');

    // 3. Web login for parent via +62 phone variant
    $this->post('/login', [
        'email' => '+6285712345678',
        'password' => 'ParentPass1!',
    ])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($parentUser);
    $this->post('/logout');

    // 4. API v1 login via NIY & NUPTK
    $this->postJson('/api/v1/login', [
        'identifier' => 'NIY-202699',
        'password' => 'Password123!',
        'device_name' => 'Hanyakra Android',
    ])->assertOk()
        ->assertJsonPath('data.user.uuid', $homeroomTeacher->uuid);

    $this->postJson('/api/v1/mobile/auth/login', [
        'email' => '1234567890123456',
        'password' => 'Password123!',
        'device_id' => 'device-teacher-1',
    ])->assertOk()
        ->assertJsonPath('data.user.id', $homeroomTeacher->id);

    // 5. API v1 login for parent via 628... phone variant
    $this->postJson('/api/v1/login', [
        'email' => '6285712345678',
        'password' => 'ParentPass1!',
    ])->assertOk()
        ->assertJsonPath('data.user.uuid', $parentUser->uuid);
});

test('user management allows creating and updating accounts without email when phone or role is provided', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Wali Murid Tanpa Email',
        'email' => '',
        'phone_number' => '+62 813-9988-7766',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'parent',
    ])->assertRedirect(route('users.index'));

    $parent = User::where('name', 'Wali Murid Tanpa Email')->firstOrFail();
    expect($parent->email)->toBeNull()
        ->and($parent->phone_number)->toBe('081399887766')
        ->and($parent->account_type)->toBe('orang_tua')
        ->and($parent->must_change_password)->toBeTrue();

    // Staff/Parent without email AND without phone is rejected
    $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Staf Tanpa Kontak',
        'email' => '',
        'phone_number' => '',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'staf_tu',
    ])->assertSessionHasErrors('email');
});

test('spmb conversion and bulk parent provisioning link siblings to one shared parent account by phone number', function () {
    $reg1 = Registration::factory()->create([
        'registration_number' => 'SPMB-2026-101',
        'full_name' => 'Kakak Pertama',
        'nisn' => '0099887701',
        'gender' => 'L',
        'parent_name' => 'Bapak Hasan',
        'parent_phone' => '081299990000',
        'status' => 'verified',
    ]);

    $reg2 = Registration::factory()->create([
        'registration_number' => 'SPMB-2026-102',
        'full_name' => 'Adik Kedua',
        'nisn' => '0099887702',
        'gender' => 'P',
        'parent_name' => 'Bapak Hasan',
        'parent_phone' => '+6281299990000',
        'status' => 'verified',
    ]);

    $service = app(RegistrationService::class);
    $res1 = $service->convertToStudent($reg1);
    $res2 = $service->convertToStudent($reg2);

    $student1 = Student::where('nisn', '0099887701')->firstOrFail();
    $student2 = Student::where('nisn', '0099887702')->firstOrFail();

    expect($res1['parent_created'])->toBeTrue()
        ->and($res1['parent_password'])->not->toBeEmpty()
        ->and($res2['parent_created'])->toBeFalse()
        ->and($student1->parent_id)->not->toBeNull()
        ->and($student1->parent_id)->toBe($student2->parent_id);

    $sharedParent = User::findOrFail($student1->parent_id);
    expect($sharedParent->phone_number)->toBe('081299990000')
        ->and($sharedParent->role)->toBe('parent')
        ->and($sharedParent->must_change_password)->toBeTrue();
});

test('homeroom teacher can reset own classroom student password and print credential sheet but cannot reset other classes or staff', function () {
    $homeroomA = User::factory()->create(['role' => 'guru']);
    $homeroomB = User::factory()->create(['role' => 'guru']);
    $tu = User::factory()->create(['role' => 'staf_tu']);

    $classroomA = Classroom::factory()->create([
        'name' => 'X-A',
        'academic_year' => '2026/2027',
        'teacher_id' => $homeroomA->id,
    ]);
    $classroomB = Classroom::factory()->create([
        'name' => 'X-B',
        'academic_year' => '2026/2027',
        'teacher_id' => $homeroomB->id,
    ]);

    $studentAUser = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $studentA = Student::factory()->create([
        'user_id' => $studentAUser->id,
        'classroom_id' => $classroomA->id,
        'nis' => '26101',
        'nisn' => '0012345101',
    ]);

    $studentBUser = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    Student::factory()->create([
        'user_id' => $studentBUser->id,
        'classroom_id' => $classroomB->id,
        'nis' => '26102',
        'nisn' => '0012345102',
    ]);

    // Homeroom A can view student accounts page & reset student A
    $this->actingAs($homeroomA)
        ->get(route('student-accounts.index', ['classroom_id' => $classroomA->id]))
        ->assertOk()
        ->assertSee($studentAUser->name);

    $resetResponse = $this->actingAs($homeroomA)
        ->postJson(route('users.reset-password', $studentAUser->id))
        ->assertOk()
        ->assertJsonPath('success', true);

    $tempPassword = (string) $resetResponse->json('data.temp_password');
    $studentAUser->refresh();
    expect($studentAUser->must_change_password)->toBeTrue()
        ->and(Hash::check($tempPassword, $studentAUser->password))->toBeTrue();

    // Homeroom A cannot reset Student B (other classroom) or TU or self
    $this->actingAs($homeroomA)
        ->postJson(route('users.reset-password', $studentBUser->id))
        ->assertForbidden();

    $this->actingAs($homeroomA)
        ->postJson(route('users.reset-password', $tu->id))
        ->assertForbidden();

    $this->actingAs($homeroomA)
        ->postJson(route('users.reset-password', $homeroomA->id))
        ->assertStatus(422);

    // Homeroom A can download PDF credential sheet for Classroom A, but 403 for Classroom B
    $pdfResponse = $this->actingAs($homeroomA)
        ->get(route('classrooms.credentials-pdf', $classroomA->id))
        ->assertOk();
    expect($pdfResponse->headers->get('content-type'))->toContain('application/pdf');

    $this->actingAs($homeroomA)
        ->get(route('classrooms.credentials-pdf', $classroomB->id))
        ->assertForbidden();

    // TU can reset Student B and Teacher B, and download any classroom PDF
    $this->actingAs($tu)
        ->postJson(route('users.reset-password', $studentBUser->id))
        ->assertOk();

    $this->actingAs($tu)
        ->postJson(route('users.reset-password', $homeroomB->id))
        ->assertOk();

    $this->actingAs($tu)
        ->get(route('classrooms.credentials-pdf', $classroomB->id))
        ->assertOk();
});

test('whatsapp otp password reset and api put me/password clear must_change_password flag', function () {
    Setting::set('whatsapp_gateway_enabled', '1');

    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => null,
        'phone_number' => '081277778888',
        'password' => Hash::make('OldTempPass1'),
        'role' => 'parent',
        'must_change_password' => true,
    ]);

    // Request OTP via API
    $this->postJson('/api/v1/auth/whatsapp-otp/request', [
        'identifier' => '081277778888',
    ])->assertOk()
        ->assertJsonPath('success', true);

    $log = WhatsappLog::where('recipient_phone', '081277778888')->latest('id')->firstOrFail();
    preg_match('/\*(\d{6})\*/', $log->message, $matches);
    $otp = $matches[1] ?? '';
    expect($otp)->toHaveLength(6);

    // Wrong OTP rejected
    $this->postJson('/api/v1/auth/whatsapp-otp/reset', [
        'identifier' => '081277778888',
        'otp' => '000000',
        'password' => 'NewSecurePass1!',
        'password_confirmation' => 'NewSecurePass1!',
    ])->assertStatus(422);

    // Correct OTP resets password and clears must_change_password
    $this->postJson('/api/v1/auth/whatsapp-otp/reset', [
        'identifier' => '081277778888',
        'otp' => $otp,
        'password' => 'NewSecurePass1!',
        'password_confirmation' => 'NewSecurePass1!',
    ])->assertOk()
        ->assertJsonPath('success', true);

    $user->refresh();
    expect($user->must_change_password)->toBeFalse()
        ->and(Hash::check('NewSecurePass1!', $user->password))->toBeTrue();

    // Now test authenticated PUT /api/v1/me/password when must_change_password = true
    $user->update(['must_change_password' => true]);
    $oldToken = $user->createToken('old-device')->plainTextToken;
    $activeToken = $user->createToken('current-device')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$activeToken)
        ->putJson('/api/v1/me/password', [
            'current_password' => 'NewSecurePass1!',
            'password' => 'UpdatedPass2026!',
            'password_confirmation' => 'UpdatedPass2026!',
        ])->assertOk()
        ->assertJsonPath('data.must_change_password', false);

    $user->refresh();
    expect($user->must_change_password)->toBeFalse()
        ->and(Hash::check('UpdatedPass2026!', $user->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(1);
});
