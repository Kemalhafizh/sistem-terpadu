<?php

use App\Models\AcademicYear;
use App\Models\BookableResource;
use App\Models\Classroom;
use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\ReportCard;
use App\Models\ResourceBooking;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ElearningMobileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('sso auto-login rejects email and role fallbacks outside local environment and prevents open redirects', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $inactive = User::factory()->create(['role' => 'student', 'account_status' => 'nonaktif']);
    $active = User::factory()->create(['role' => 'student', 'account_status' => 'aktif']);

    // Email/role demo fallback must not sign users in under testing/production.
    $this->get(route('sso.auto_login', ['email' => $admin->email, 'role' => 'admin']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    // Nonaktif user with a real Sanctum token is still denied.
    $inactiveToken = $inactive->createToken('mobile')->plainTextToken;
    $this->get(route('sso.auto_login', ['token' => $inactiveToken]))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    // Valid token logs in and strips protocol-relative open redirect paths (//evil.com).
    $validToken = $active->createToken('mobile')->plainTextToken;
    $this->get(route('sso.auto_login', ['token' => $validToken, 'redirect' => '//evil.example/phish']))
        ->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($active);
});

test('nonaktif accounts are rejected on web, api logins, and active sanctum tokens', function () {
    $inactive = User::factory()->create([
        'role' => 'student',
        'account_status' => 'nonaktif',
    ]);

    // 1. Web login blocked
    $this->post('/login', [
        'email' => $inactive->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();

    // 2. Existing web session terminated by EnsureActiveAccount
    $this->actingAs($inactive)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    // 3. API v1 login blocked
    $this->postJson('/api/v1/login', [
        'email' => $inactive->email,
        'password' => 'password',
        'device_name' => 'Android',
    ])->assertStatus(403);

    // 4. Mobile v1 auth login blocked
    $this->postJson('/api/v1/mobile/auth/login', [
        'email' => $inactive->email,
        'password' => 'password',
        'device_id' => 'device-1',
        'platform' => 'android',
        'fcm_token' => 'fcm-token-123',
    ])->assertStatus(403)
        ->assertJson(['code' => 'ACCOUNT_INACTIVE']);

    // 5. Existing Sanctum bearer token rejected & revoked
    $plainTextToken = $inactive->createToken('device-1')->plainTextToken;
    $this->withToken($plainTextToken)
        ->getJson('/api/v1/me')
        ->assertStatus(403)
        ->assertJson(['code' => 'ACCOUNT_INACTIVE']);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $inactive->id,
        'tokenable_type' => User::class,
    ]);
});

test('session fingerprint tolerates roaming IP changes but terminates User-Agent changes', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->post('/login', [
        'email' => $student->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    // Initial dashboard hit binds fingerprint to UA.
    $this->withHeaders(['User-Agent' => 'HanyakraBrowser/1.0'])
        ->withServerVariables(['REMOTE_ADDR' => '114.10.20.30'])
        ->get('/dashboard')
        ->assertOk();

    // Switching cellular subnet stays valid with same User-Agent.
    $this->withHeaders(['User-Agent' => 'HanyakraBrowser/1.0'])
        ->withServerVariables(['REMOTE_ADDR' => '182.200.55.9'])
        ->get('/dashboard')
        ->assertOk();
    $this->assertAuthenticatedAs($student);

    // Changing User-Agent invalidates session.
    $this->withHeaders(['User-Agent' => 'HijackerBot/2.0'])
        ->withServerVariables(['REMOTE_ADDR' => '182.200.55.9'])
        ->get('/dashboard')
        ->assertRedirect(route('login'));
    $this->assertGuest();
});

test('admin cannot escalate to or tamper with superadmin or delete self, while superadmin can', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $admin = User::factory()->create(['role' => 'admin']);
    $guru = User::factory()->create(['role' => 'guru']);

    // Admin cannot create superadmin
    $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Rogue Super',
        'email' => 'rogue@example.com',
        'password' => 'Password123',
        'role' => 'superadmin',
    ])->assertForbidden();

    // Admin cannot edit superadmin
    $this->actingAs($admin)->get(route('users.edit', $superadmin->id))->assertForbidden();

    // Admin cannot promote guru to superadmin
    $this->actingAs($admin)->put(route('users.update', $guru->id), [
        'name' => $guru->name,
        'email' => $guru->email,
        'role' => 'superadmin',
    ])->assertForbidden();

    // Admin cannot update or delete superadmin or self
    $this->actingAs($admin)->put(route('users.update', $superadmin->id), [
        'name' => 'Hacked',
        'email' => $superadmin->email,
        'role' => 'admin',
    ])->assertForbidden();
    $this->actingAs($admin)->delete(route('users.destroy', $superadmin->id))->assertForbidden();
    $this->actingAs($admin)->delete(route('users.destroy', $admin->id))->assertForbidden();

    // Superadmin succeeds creating another superadmin
    $this->actingAs($superadmin)->post(route('users.store'), [
        'name' => 'Second Superadmin',
        'email' => 'super2@example.com',
        'password' => 'Password123',
        'role' => 'superadmin',
    ])->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', ['email' => 'super2@example.com', 'role' => 'superadmin']);
});

test('question bank and resource booking routes enforce roles and ownership', function () {
    $student = User::factory()->create(['role' => 'student']);
    $guruA = User::factory()->create(['role' => 'guru']);
    $guruB = User::factory()->create(['role' => 'guru']);
    $wakaKurikulum = User::factory()->create(['role' => 'waka_kurikulum']);
    $wakaSarpras = User::factory()->create(['role' => 'waka_sarpras']);
    $subject = Subject::factory()->create();

    $question = QuestionBank::create([
        'subject_id' => $subject->id,
        'created_by' => $guruA->id,
        'question_text' => 'Siapa pendiri sekolah?',
        'type' => 'multiple_choice',
        'options' => ['A' => 'Satu', 'B' => 'Dua'],
        'correct_answer' => 'A',
        'difficulty' => 'easy',
        'grade_level' => 'X',
    ]);

    $resource = BookableResource::create([
        'name' => 'Lab Fisika',
        'type' => 'room',
        'capacity' => 40,
        'is_available' => true,
    ]);
    $booking = ResourceBooking::create([
        'bookable_resource_id' => $resource->id,
        'booked_by' => $guruA->id,
        'purpose' => 'Praktikum',
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(2),
        'status' => 'pending',
    ]);

    // Student blocked on QuestionBank & ResourceBooking
    $this->actingAs($student)->get(route('cbt.question-banks.index'))->assertForbidden();
    $this->actingAs($student)->get(route('cbt.question-banks.create'))->assertForbidden();
    $this->actingAs($student)->post(route('resource_bookings.approve', $booking->id))->assertForbidden();
    $this->actingAs($student)->post(route('resource_bookings.reject', $booking->id))->assertForbidden();

    // GuruA can access, GuruB cannot edit/destroy GuruA's question or approve booking
    $this->actingAs($guruB)->get(route('cbt.question-banks.edit', $question))->assertForbidden();
    $this->actingAs($guruB)->delete(route('cbt.question-banks.destroy', $question))->assertForbidden();
    $this->actingAs($guruB)->post(route('resource_bookings.approve', $booking->id))->assertForbidden();

    // Waka Kurikulum can edit question; Waka Sarpras can approve booking
    $this->actingAs($wakaKurikulum)->get(route('cbt.question-banks.edit', $question))->assertOk();
    $this->actingAs($wakaSarpras)->post(route('resource_bookings.approve', $booking->id))->assertRedirect();
    expect($booking->fresh()->status)->toBe('approved');
});

test('report card routes restrict BK, non-homeroom teachers, and other parents while allowing own parent', function () {
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $homeroomTeacher = User::factory()->create(['role' => 'guru']);
    $otherGuru = User::factory()->create(['role' => 'guru']);
    $bk = User::factory()->create(['role' => 'bk']);
    $parent1 = User::factory()->create(['role' => 'parent']);
    $otherParent = User::factory()->create(['role' => 'parent']);

    $classroom = Classroom::factory()->create([
        'academic_year' => $year->year,
        'teacher_id' => $homeroomTeacher->id,
    ]);
    $student1 = Student::factory()->create([
        'classroom_id' => $classroom->id,
        'parent_id' => $parent1->id,
    ]);
    $reportCard = ReportCard::create([
        'student_id' => $student1->id,
        'academic_year_id' => $year->id,
        'classroom_id' => $classroom->id,
        'total_score' => 90,
        'average_score' => 90,
        'rank_in_class' => 1,
    ]);

    // BK cannot open ledger index
    $this->actingAs($bk)->get(route('report-cards.index'))->assertForbidden();

    // Non-homeroom teacher cannot sync or save homeroom notes
    $this->actingAs($otherGuru)->post(route('report-cards.sync'), [
        'classroom_id' => $classroom->id,
    ])->assertForbidden();
    $this->actingAs($otherGuru)->post(route('report-cards.store-notes'), [
        'report_cards' => [$reportCard->id => ['homeroom_notes' => 'Tampered']],
    ])->assertForbidden();

    // Own homeroom teacher can save notes
    $this->actingAs($homeroomTeacher)->post(route('report-cards.store-notes'), [
        'report_cards' => [$reportCard->id => ['homeroom_notes' => 'Bagus sekali']],
    ])->assertRedirect();
    expect($reportCard->fresh()->homeroom_notes)->toBe('Bagus sekali');

    // Own parent can download PDF; unrelated parent gets 403
    $this->actingAs($parent1)->get(route('report-cards.pdf', $student1))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
    $this->actingAs($otherParent)->get(route('report-cards.pdf', $student1))->assertForbidden();
});

test('cbt web routes enforce creation ordering, role gates, and isolate student results', function () {
    $year = AcademicYear::factory()->create(['is_active' => true]);
    $classroom = Classroom::factory()->create(['academic_year' => $year->year]);
    $subject = Subject::factory()->create();
    $creatorGuru = User::factory()->create(['role' => 'guru']);
    $otherGuru = User::factory()->create(['role' => 'guru']);

    $studentUser1 = User::factory()->create(['role' => 'siswa']);
    $student1 = Student::factory()->create(['user_id' => $studentUser1->id, 'classroom_id' => $classroom->id]);
    $studentUser2 = User::factory()->create(['role' => 'student']);
    $student2 = Student::factory()->create(['user_id' => $studentUser2->id, 'classroom_id' => $classroom->id]);

    $exam = Exam::factory()->create([
        'subject_id' => $subject->id,
        'classroom_id' => $classroom->id,
        'academic_year_id' => $year->id,
        'created_by' => $creatorGuru->id,
        'status' => 'published',
    ]);

    ExamAttempt::create(['exam_id' => $exam->id, 'student_id' => $student1->id, 'score' => 88, 'status' => 'graded']);
    ExamAttempt::create(['exam_id' => $exam->id, 'student_id' => $student2->id, 'score' => 95, 'status' => 'graded']);

    // cbt.exams.create must match before cbt.exams.show
    $this->actingAs($creatorGuru)->get(route('cbt.exams.create'))->assertOk();

    // Unrelated guru is refused
    $this->actingAs($otherGuru)->get(route('cbt.exams.show', $exam))->assertForbidden();
    $this->actingAs($otherGuru)->get(route('cbt.exams.edit', $exam))->assertForbidden();
    $this->actingAs($otherGuru)->get(route('cbt.exams.results', $exam))->assertForbidden();
    $this->actingAs($otherGuru)->get(route('cbt.exams.monitor', $exam))->assertForbidden();

    // Student cannot author or monitor exams, and results view only receives student1's attempt
    $this->actingAs($studentUser1)->get(route('cbt.exams.create'))->assertForbidden();
    $this->actingAs($studentUser1)->get(route('cbt.exams.monitor', $exam))->assertForbidden();

    $response = $this->actingAs($studentUser1)->get(route('cbt.exams.results', $exam))->assertOk();
    $attemptStudentIds = collect($response->viewData('attempts')->items())->pluck('student_id')->all();
    expect($attemptStudentIds)->toBe([$student1->id]);
});

test('mobile data and viz endpoints refuse non-academic roles and students without profile', function () {
    $merchant = User::factory()->create(['role' => 'merchant']);
    $alumni = User::factory()->create(['role' => 'alumni']);
    $orphanStudentUser = User::factory()->create(['role' => 'student']);
    $parent1 = User::factory()->create(['role' => 'parent']);
    $parent2 = User::factory()->create(['role' => 'parent']);
    $student = Student::factory()->create(['parent_id' => $parent1->id]);

    Sanctum::actingAs($merchant);
    $this->getJson('/api/v1/students')->assertForbidden();
    $this->getJson("/api/v1/viz/radar/student/{$student->id}")->assertForbidden();

    Sanctum::actingAs($alumni);
    $this->getJson('/api/v1/students')->assertForbidden();
    $this->getJson("/api/v1/viz/radar/student/{$student->id}")->assertForbidden();

    Sanctum::actingAs($orphanStudentUser);
    $this->getJson('/api/v1/viz/radar/student/1')->assertForbidden();

    Sanctum::actingAs($parent2);
    $this->getJson("/api/v1/viz/radar/student/{$student->id}")->assertForbidden();

    Sanctum::actingAs($parent1);
    $this->getJson("/api/v1/viz/radar/student/{$student->id}")->assertOk();
});

test('mobile elearning scopes classes by classroom, blocks orphan submission, and validates material mimes', function () {
    Storage::fake('public');

    $guru = User::factory()->create(['role' => 'guru']);
    $subject = Subject::factory()->create();
    $classroomA = Classroom::factory()->create();
    $classroomB = Classroom::factory()->create();

    $studentUser = User::factory()->create(['role' => 'student']);
    Student::factory()->create(['user_id' => $studentUser->id, 'classroom_id' => $classroomA->id]);
    $orphanStudentUser = User::factory()->create(['role' => 'student']);

    $classA = ElearningClass::create([
        'uuid' => (string) Str::uuid(),
        'subject_id' => $subject->id,
        'classroom_id' => $classroomA->id,
        'teacher_id' => $guru->id,
        'name' => 'Kelas A',
        'is_archived' => false,
    ]);
    $classB = ElearningClass::create([
        'uuid' => (string) Str::uuid(),
        'subject_id' => $subject->id,
        'classroom_id' => $classroomB->id,
        'teacher_id' => $guru->id,
        'name' => 'Kelas B',
        'is_archived' => false,
    ]);
    $assignmentA = ElearningAssignment::create([
        'uuid' => (string) Str::uuid(),
        'elearning_class_id' => $classA->id,
        'title' => 'Tugas A',
        'description' => 'Kerjakan',
        'due_at' => now()->addDay(),
    ]);

    // Student receives only classA, never classB
    Sanctum::actingAs($studentUser);
    $classIds = collect($this->getJson('/api/v1/elearning/classes/student')->assertOk()->json('data'))
        ->pluck('id')
        ->all();
    expect($classIds)->toBe([$classA->id]);

    // Missing student profile fails closed with 403 before storing upload
    expect(fn () => app(ElearningMobileService::class)->submitAssignment(
        $assignmentA->id,
        $orphanStudentUser->id,
        'Jawaban',
        UploadedFile::fake()->create('tugas.pdf', 100)
    ))->toThrow(HttpException::class);

    // Unsafe material upload extensions rejected
    Sanctum::actingAs($guru);
    $this->postJson("/api/v1/elearning/classes/{$classA->id}/materials", [
        'title' => 'Payload',
        'type' => 'document',
        'file' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
    ])->assertStatus(422)->assertJsonValidationErrors('file');
});

test('mobile config uses Hanyakra branding and disables incomplete spp payment gateway flag', function () {
    $response = $this->getJson('/api/v1/mobile/config')->assertOk();

    expect((string) $response->json('app_name'))->toStartWith('Hanyakra ')
        ->and($response->json('features.spp_payment_gateway'))->toBeFalse();
});
