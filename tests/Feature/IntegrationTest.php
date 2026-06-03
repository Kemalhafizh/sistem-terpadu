<?php

use App\Models\User;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Classroom;
use App\Models\IntegrationConfig;
use App\Models\IntegrationLog;
use App\Models\Webhook;
use App\Services\Integration\DapodikService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

beforeEach(function () {
    // Truncate tables for a clean state
    Webhook::truncate();
    IntegrationLog::truncate();
    IntegrationConfig::truncate();
    Student::truncate();
    Employee::truncate();
    Classroom::truncate();
    User::truncate();
});

test('guru cannot access integrations portal due to RBAC restriction', function () {
    $guru = new User();
    $guru->name = 'Guru Test';
    $guru->email = 'guru@sista.sch.id';
    $guru->password = bcrypt('password');
    $guru->must_change_password = false;
    $guru->preferred_locale = 'id';
    $guru->preferred_theme = 'dark';
    $guru->role = 'guru';
    $guru->save();

    $response = $this->actingAs($guru)->get(route('admin.integrations.index'));
    
    $response->assertStatus(403);
});

test('admin can access integrations dashboard successfully', function () {
    $admin = new User();
    $admin->name = 'Admin Sista';
    $admin->email = 'admin@sista.sch.id';
    $admin->password = bcrypt('password');
    $admin->must_change_password = false;
    $admin->preferred_locale = 'id';
    $admin->preferred_theme = 'dark';
    $admin->role = 'admin';
    $admin->save();

    $response = $this->actingAs($admin)->get(route('admin.integrations.index'));
    
    $response->assertStatus(200)
             ->assertViewIs('integrations.index');
});

test('dapodik service validates invalid student nisn format correctly', function () {
    $service = new DapodikService();

    // Data with invalid NISN (less than 10 digits)
    $invalidData = [
        [
            'nisn' => '123',
            'nama' => 'Ahmad Fauzi',
            'tanggal_lahir' => '2010-05-12',
            'kelas' => 'X-A'
        ]
    ];

    $warnings = $service->validateAgainstDapodik('students', $invalidData);

    expect($warnings)->toHaveCount(1);
    expect($warnings[0]['identifier'])->toBe('Ahmad Fauzi');
    expect($warnings[0]['errors'][0])->toBe('NISN harus tepat 10 digit angka.');
});

test('admin can download students dapodik excel export successfully and write audit log', function () {
    $admin = new User();
    $admin->name = 'Admin Sista';
    $admin->email = 'admin@sista.sch.id';
    $admin->password = bcrypt('password');
    $admin->must_change_password = false;
    $admin->preferred_locale = 'id';
    $admin->preferred_theme = 'dark';
    $admin->role = 'admin';
    $admin->save();

    $config = new IntegrationConfig();
    $config->provider = 'dapodik';
    $config->name = 'Koneksi Dapodik';
    $config->is_active = true;
    $config->save();

    // Seed a valid classroom and student
    $classroom = new Classroom();
    $classroom->name = 'X-A';
    $classroom->grade_level = 'X';
    $classroom->major = 'MIPA';
    $classroom->academic_year = '2026/2027';
    $classroom->save();

    $user = new User();
    $user->name = 'Siswa Baik';
    $user->email = 'siswa@sista.sch.id';
    $user->password = bcrypt('password');
    $user->must_change_password = false;
    $user->preferred_locale = 'id';
    $user->preferred_theme = 'dark';
    $user->role = 'student';
    $user->save();

    $student = new Student();
    $student->user_id = $user->id;
    $student->classroom_id = $classroom->id;
    $student->nis = '100234';
    $student->nisn = '0098765432'; // Valid 10 digit NISN
    $student->gender = 'L';
    $student->birth_date = '2010-02-15';
    $student->save();

    $response = $this->actingAs($admin)->post(route('admin.integrations.dapodik.export'), [
        'type' => 'students',
        'classroom_id' => $classroom->id
    ]);

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // Assert audit log was generated
    $log = IntegrationLog::first();
    expect($log)->not->toBeNull();
    expect($log->records_processed)->toBe(1);
    expect($log->status)->toBe('success');
});

test('admin can register new webhook outgoing successfully', function () {
    $admin = new User();
    $admin->name = 'Admin Sista';
    $admin->email = 'admin@sista.sch.id';
    $admin->password = bcrypt('password');
    $admin->must_change_password = false;
    $admin->preferred_locale = 'id';
    $admin->preferred_theme = 'dark';
    $admin->role = 'admin';
    $admin->save();

    $response = $this->actingAs($admin)->post(route('admin.integrations.webhooks.store'), [
        'name' => 'Sync Yayasan Baru',
        'url' => 'https://yayasan-external.id/api/incoming-siswa',
        'secret' => 'super_secret_token',
        'events' => ['student.created']
    ]);

    $response->assertRedirect(route('admin.integrations.webhooks.index'));
    
    $webhook = Webhook::first();
    expect($webhook)->not->toBeNull();
    expect($webhook->name)->toBe('Sync Yayasan Baru');
    expect($webhook->url)->toBe('https://yayasan-external.id/api/incoming-siswa');
    expect($webhook->events)->toContain('student.created');
});

test('admin can trigger test ping webhook which returns json response', function () {
    $admin = new User();
    $admin->name = 'Admin Sista';
    $admin->email = 'admin@sista.sch.id';
    $admin->password = bcrypt('password');
    $admin->must_change_password = false;
    $admin->preferred_locale = 'id';
    $admin->preferred_theme = 'dark';
    $admin->role = 'admin';
    $admin->save();

    $webhook = new Webhook();
    $webhook->name = 'Mock Endpoint Yayasan';
    $webhook->url = 'https://yayasan.sch.id/webhook';
    $webhook->events = ['student.created'];
    $webhook->is_active = true;
    $webhook->save();

    // Fake HTTP Client to intercept outgoing request
    Http::fake([
        'https://yayasan.sch.id/*' => Http::response(['message' => 'Received Successfully'], 200)
    ]);

    // Use full Webhook object for route model binding resolution (UUID)
    $response = $this->actingAs($admin)->post(route('admin.integrations.webhooks.test', $webhook));

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'status',
                 'successful',
                 'latency_ms',
                 'body'
             ]);

    $data = $response->json();
    expect($data['status'])->toBe(200);
    expect($data['successful'])->toBeTrue();
    expect($data['body'])->toContain('Received Successfully');
});
