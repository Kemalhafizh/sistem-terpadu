<?php

use App\Models\User;
use App\Models\Student;
use App\Models\ElearningClass;
use App\Models\ElearningMaterial;
use App\Models\ElearningMaterialComment;
use App\Models\Registration;
use App\Models\Billing;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guruA = User::factory()->create(['role' => 'guru']);
    $this->guruB = User::factory()->create(['role' => 'guru']);
    $this->studentA = User::factory()->create(['role' => 'student']);
    $this->studentB = User::factory()->create(['role' => 'student']);
    $this->parentA = User::factory()->create(['role' => 'parent']);
    $this->parentB = User::factory()->create(['role' => 'parent']);

    // Setup active AcademicYear, Classrooms, and Subject
    $this->academicYear = \App\Models\AcademicYear::factory()->create(['is_active' => true]);
    $this->classroomA = \App\Models\Classroom::factory()->create();
    $this->classroomB = \App\Models\Classroom::factory()->create();
    $this->subject = \App\Models\Subject::factory()->create();

    // Setup Student profiles linked to users
    $this->profileA = Student::factory()->create([
        'user_id' => $this->studentA->id,
        'classroom_id' => $this->classroomA->id,
        'parent_id' => $this->parentA->id,
    ]);
    $this->profileB = Student::factory()->create([
        'user_id' => $this->studentB->id,
        'classroom_id' => $this->classroomB->id,
        'parent_id' => $this->parentB->id,
    ]);

    // Create Elearning Classes
    $this->classA = ElearningClass::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'subject_id' => $this->subject->id,
        'classroom_id' => $this->classroomA->id,
        'teacher_id' => $this->guruA->id,
        'name' => 'Kelas Fisika Guru A',
        'description' => 'Deskripsi Fisika',
        'is_archived' => false,
    ]);

    $this->materialA = ElearningMaterial::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'elearning_class_id' => $this->classA->id,
        'title' => 'Materi Gerak Lurus',
        'description' => 'Deskripsi Materi',
    ]);
});

// ── E-LEARNING BOLA / IDOR TESTS ──────────────────────────────────────

test('student in other class cannot view class details', function () {
    // Student B is in classroom 2, Class A is in classroom 1
    $response = $this->actingAs($this->studentB)
        ->get(route('elearning.class.show', $this->classA->uuid));

    $response->assertStatus(403);
});

test('student in same class can view class details', function () {
    $response = $this->actingAs($this->studentA)
        ->get(route('elearning.class.show', $this->classA->uuid));

    $response->assertStatus(200);
});

test('guru who does not teach the class cannot add material', function () {
    $response = $this->actingAs($this->guruB)
        ->post(route('elearning.material.store', $this->classA->uuid), [
            'title' => 'Materi Ilegal',
            'description' => 'Mencoba mengunggah materi',
        ]);

    $response->assertStatus(403);
});

test('unauthorized user cannot post comments to material', function () {
    $response = $this->actingAs($this->studentB)
        ->post(route('elearning.comment.store', $this->materialA), [
            'content' => 'Komentar Spam',
        ]);

    $response->assertStatus(403);
});

test('comments content is sanitized from dangerous html tags', function () {
    $dangerousHtml = 'Halo <script>alert("hack")</script> <iframe src="dangerous"></iframe> <b>Tebal</b>';
    
    $response = $this->actingAs($this->studentA)
        ->post(route('elearning.comment.store', $this->materialA), [
            'content' => $dangerousHtml,
        ]);

    $response->assertStatus(302); // Redirect back
    
    $comment = ElearningMaterialComment::latest()->first();
    expect($comment->content)->not->toContain('<script>');
    expect($comment->content)->not->toContain('<iframe>');
    expect($comment->content)->toContain('<b>Tebal</b>');
});

// ── SPMB IDOR TESTS ──────────────────────────────────────────────────

test('unauthorized guest cannot print arbitrary registration card', function () {
    $registration = Registration::create([
        'registration_number' => 'SPMB-2026-9999',
        'full_name' => 'Siswa Rahasia',
        'email' => 'rahasi@siswa.com',
        'nisn' => '1234567890',
        'previous_school' => 'SMP 1',
        'parent_phone' => '081234',
        'gender' => 'L',
        'birth_date' => '2010-01-01',
        'birth_place' => 'Semarang',
        'address' => 'Jalan SA',
        'parent_name' => 'Wali',
        'document_kk' => 'kk.png',
        'document_certificate' => 'cert.png',
        'academic_year_id' => $this->academicYear->id,
    ]);

    $response = $this->get(route('spmb.print-card', $registration->uuid));
    $response->assertStatus(403);
});

// ── IMPORT EXPORT PATH TRAVERSAL TESTS ──────────────────────────────

test('execute import rejects path traversal in file_path', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($user)
        ->post(route('import-export.execute'), [
            'module' => 'students',
            'file_path' => 'temp_imports/../../../.env',
            'file_name' => 'fake.csv',
            'mapping' => ['full_name' => 0],
        ]);

    $response->assertRedirect(route('import-export.index'));
    $response->assertSessionHas('error');
});

// ── MIDTRANS SIGNATURE BYPASS / SPOOFING TESTS ──────────────────────

test('midtrans webhook rejects simulation bypass in production environment', function () {
    // Force application environment to production
    app()->detectEnvironment(fn() => 'production');
    app()->offsetSet('env', 'production');
    config(['services.midtrans.server_key' => 'mock-server-key']);
    
    $category = \App\Models\PaymentCategory::factory()->create();
    
    $billing = Billing::create([
        'student_id' => $this->profileA->id,
        'payment_category_id' => $category->id,
        'amount' => 500000,
        'due_date' => '2026-12-31',
        'academic_year_id' => $this->academicYear->id,
        'status' => 'unpaid',
    ]);

    $response = $this->postJson(route('api.midtrans.webhook'), [
        'order_id' => 'BILL-' . $billing->id . '-123456',
        'status_code' => '200',
        'gross_amount' => '500000',
        'transaction_status' => 'settlement',
        'is_simulation' => true,
    ]);

    // Expected 403 Forbidden because signature verification is required and signature_key was not sent/correct
    $response->assertStatus(403);
});

// ── ENTERPRISE HARDENING TESTS (NEW) ────────────────────────────────

test('security headers are strictly enforced', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    // Bandingkan CSP secara manual
    expect($response->headers->has('Content-Security-Policy'))->toBeTrue();
});

test('student address is encrypted in database but decrypted automatically', function () {
    $plainAddress = 'Jalan Sultan Agung No. 1 Semarang';

    $student = Student::factory()->create([
        'user_id' => $this->studentA->id,
        'classroom_id' => $this->classroomA->id,
        'parent_id' => $this->parentA->id,
        'address' => $plainAddress,
    ]);

    // Ambil data mentah langsung dari database untuk memverifikasi enkripsi
    $rawDbRow = \Illuminate\Support\Facades\DB::table('students')->where('id', $student->id)->first();
    
    // Pastikan data di database tidak berupa plain text
    expect($rawDbRow->address)->not->toBe($plainAddress);
    
    // Pastikan data didekripsi otomatis saat diakses melalui model Eloquent
    expect($student->fresh()->address)->toBe($plainAddress);
});

test('verify mfa middleware redirects administrative user when mfa is not enabled', function () {
    // Buat route temporer untuk menguji middleware VerifyMfa
    Route::get('/test-mfa-protection', function () {
        return 'success';
    })->middleware(['web', 'mfa']);

    $adminUser = User::factory()->create([
        'role' => 'admin',
        'two_factor_code' => null, // Mensimulasikan MFA belum aktif (two_factor_code kosong)
    ]);

    $response = $this->actingAs($adminUser)->get('/test-mfa-protection');
    
    // Harus dialihkan ke halaman setup/verify fallback yang ada
    $response->assertRedirect(route('verify-2fa.index'));
});

test('admin ip whitelist middleware blocks non whitelisted ip addresses', function () {
    Route::get('/admin/test-ip-protection', function () {
        return 'admin secret data';
    })->middleware(['web', 'admin.ip']);

    // Force env to non-testing to trigger strict IP validation logic
    app()->detectEnvironment(fn() => 'production');
    
    // Set allowed IP into env config
    config(['app.env' => 'production']);
    putenv('ADMIN_ALLOWED_IPS=192.168.1.100');

    $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
        ->get('/admin/test-ip-protection');

    $response->assertStatus(403);
});

test('security check dns command executes successfully', function () {
    $this->artisan('security:check-dns', ['domain' => 'sultanagung1.sch.id'])
        ->assertExitCode(0);
});

test('secure backup command executes 3-2-1 strategy successfully', function () {
    $this->artisan('backup:secure', ['--destination' => 'local'])
        ->assertExitCode(0);
});

test('disaster recovery test command executes restore drill successfully', function () {
    $this->artisan('backup:restore-test')
        ->assertExitCode(0);
});

