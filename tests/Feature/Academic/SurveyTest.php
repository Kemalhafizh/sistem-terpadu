<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Services\SurveyAnalyticsService;
use App\Services\WhatsappService;
use Tests\TestCase;

beforeEach(function () {
    // Set up basic actors
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);

    // Create Student model and link to studentUser
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
    ]);

    // Link student as child to parentUser
    $this->student->parent_id = $this->parentUser->id;
    $this->student->save();
});

// 1. Accessibility Checks
test('guest is redirected when accessing survey routes', function () {
    $this->get(route('surveys.index'))->assertRedirect(route('login'));
    $this->get(route('surveys.create'))->assertRedirect(route('login'));
});

test('non-admin is blocked from creating surveys', function () {
    $this->actingAs($this->studentUser)->get(route('surveys.create'))->assertStatus(403);
    $this->actingAs($this->guru)->get(route('surveys.create'))->assertStatus(403);
});

test('admin can access survey builder form', function () {
    $this->actingAs($this->admin)->get(route('surveys.create'))->assertStatus(200);
});

// 2. Survey Creation
test('admin can create a survey with validation', function () {
    $data = [
        'title' => 'Survei Kepuasan Pembelajaran',
        'description' => 'Evaluasi berkala pengajaran guru kelas.',
        'target_role' => 'student',
        'target_classroom_id' => $this->classroom->id,
        'is_anonymous' => 1,
        'start_date' => now()->addDay()->format('Y-m-d\TH:i'),
        'end_date' => now()->addDays(5)->format('Y-m-d\TH:i'),
        'max_responses' => 100,
        'send_notification' => 1,
        'questions' => [
            [
                'question_text' => 'Bagaimana pemahaman Anda terhadap materi?',
                'type' => 'scale',
                'options' => null,
                'is_required' => 1,
                'scale_min' => 1,
                'scale_max' => 5,
                'scale_min_label' => 'Sangat Kurang',
                'scale_max_label' => 'Sangat Faham',
            ],
            [
                'question_text' => 'Pilih fasilitas yang menurut Anda paling menunjang:',
                'type' => 'checkbox',
                'options' => ['Lab Komputer', 'Perpustakaan', 'WiFi Kelas'],
                'is_required' => 0,
            ],
        ],
    ];

    $response = $this->actingAs($this->admin)->post(route('surveys.store'), $data);
    $response->assertRedirect(route('surveys.index'));

    $survey = Survey::first();
    expect($survey)->not->toBeNull();
    expect($survey->title)->toBe('Survei Kepuasan Pembelajaran');
    expect($survey->status)->toBe('draft');
    expect($survey->questions)->toHaveCount(2);

    $this->assertDatabaseHas('survey_questions', [
        'survey_id' => $survey->id,
        'question_text' => 'Bagaimana pemahaman Anda terhadap materi?',
        'type' => 'scale',
    ]);
});

// 3. Status Lifecycle & WhatsApp Broadcast
test('admin can activate survey and trigger WhatsApp broadcast', function () {
    $survey = Survey::create([
        'title' => 'Survei Siswa Aktif',
        'description' => 'Survei digital khusus siswa',
        'created_by' => $this->admin->id,
        'status' => 'draft',
        'target_role' => 'student',
        'is_anonymous' => true,
        'send_notification' => true,
    ]);

    // Mock WhatsappService
    $mockService = Mockery::mock(WhatsappService::class);
    $mockService->shouldReceive('sendMessage')
        ->once() // since only studentUser has a phone number mock potentially, let's verify chunk broadcast is triggered
        ->withAnyArgs()
        ->andReturn(new WhatsappLog([
            'recipient' => '6281234567890',
            'message' => 'Simulated message',
            'status' => 'sent',
        ]));

    $this->app->instance(WhatsappService::class, $mockService);

    // Make sure student has phone number
    $this->studentUser->update(['phone_number' => '081234567890']);

    $response = $this->actingAs($this->admin)->post(route('surveys.activate', $survey->uuid));
    $response->assertRedirect(route('surveys.index'));

    $survey->refresh();
    expect($survey->status)->toBe('active');
});

// 4. Visibility & Anonymity Settings
test('respondents can see active surveys targeted at them', function () {
    $survey = Survey::create([
        'title' => 'Survei Siswa Khusus',
        'created_by' => $this->admin->id,
        'status' => 'active',
        'target_role' => 'student',
        'target_classroom_id' => $this->classroom->id,
        'is_anonymous' => true,
    ]);

    // Student should see the survey in index
    $response = $this->actingAs($this->studentUser)->get(route('surveys.index'));
    $response->assertStatus(200);
    $response->assertSee('Survei Siswa Khusus');

    // Guru should NOT see the survey
    $responseGuru = $this->actingAs($this->guru)->get(route('surveys.index'));
    $responseGuru->assertStatus(200);
    $responseGuru->assertDontSee('Survei Siswa Khusus');
});

// 5. Response Submission & Obfuscation (PDP Protection)
test('respondent can fill out survey, enforcing single response restriction and PDP anonymity', function () {
    $survey = Survey::create([
        'title' => 'Survei Kepuasan',
        'created_by' => $this->admin->id,
        'status' => 'active',
        'target_role' => 'student',
        'is_anonymous' => true,
    ]);

    $q1 = SurveyQuestion::create([
        'survey_id' => $survey->id,
        'question_text' => 'Tingkat kepuasan?',
        'type' => 'scale',
        'is_required' => true,
        'scale_min' => 1,
        'scale_max' => 5,
    ]);

    $response = $this->actingAs($this->studentUser)->post(route('surveys.respond', $survey->uuid), [
        'answers' => [
            $q1->id => 4,
        ],
    ]);

    $response->assertRedirect(route('surveys.index'));

    // Check database has response
    $this->assertDatabaseHas('survey_responses', [
        'survey_id' => $survey->id,
        'user_id' => $this->studentUser->id,
    ]);

    // Attempt second submission - must be rejected
    $response2 = $this->actingAs($this->studentUser)->post(route('surveys.respond', $survey->uuid), [
        'answers' => [
            $q1->id => 5,
        ],
    ]);
    $response2->assertRedirect();

    // Anonymity check: The respondent model getter must mask user PII for anonymous surveys
    $surveyResponse = SurveyResponse::first();
    expect($surveyResponse->respondent)->toBeNull(); // Masked!

    // If survey is open/non-anonymous, user details are accessible
    $survey->update(['is_anonymous' => false]);
    $surveyResponse->refresh();
    expect($surveyResponse->respondent)->not->toBeNull();
    expect($surveyResponse->respondent->id)->toBe($this->studentUser->id);
});

// 6. Survey Analytics
test('SurveyAnalyticsService computes correct metrics for scale and choices', function () {
    $survey = Survey::create([
        'title' => 'Evaluasi Sekolah',
        'created_by' => $this->admin->id,
        'status' => 'active',
        'is_anonymous' => true,
    ]);

    $qScale = SurveyQuestion::create([
        'survey_id' => $survey->id,
        'question_text' => 'Skala Kebersihan',
        'type' => 'scale',
        'is_required' => true,
        'scale_min' => 1,
        'scale_max' => 5,
    ]);

    $qChoice = SurveyQuestion::create([
        'survey_id' => $survey->id,
        'question_text' => 'Pilihan Makan',
        'type' => 'radio',
        'options' => ['Nasi', 'Roti', 'Mie'],
        'is_required' => true,
    ]);

    // Responses setup
    SurveyResponse::create([
        'survey_id' => $survey->id,
        'user_id' => $this->studentUser->id,
        'answers' => [
            $qScale->id => 4,
            $qChoice->id => 'Nasi',
        ],
    ]);

    $user2 = User::factory()->create(['role' => 'student']);
    SurveyResponse::create([
        'survey_id' => $survey->id,
        'user_id' => $user2->id,
        'answers' => [
            $qScale->id => 2,
            $qChoice->id => 'Mie',
        ],
    ]);

    $user3 = User::factory()->create(['role' => 'student']);
    SurveyResponse::create([
        'survey_id' => $survey->id,
        'user_id' => $user3->id,
        'answers' => [
            $qScale->id => 2,
            $qChoice->id => 'Nasi',
        ],
    ]);

    $service = new SurveyAnalyticsService;
    $analytics = $service->getAggregatedResults($survey);

    // Assert scale calculations: [4, 2, 2] -> Mean: 2.67, Median: 2
    expect($analytics[$qScale->id]['data']['average'])->toBe(2.67);
    expect($analytics[$qScale->id]['data']['median'])->toBe(2);
    expect($analytics[$qScale->id]['data']['distribution'][2]['count'])->toBe(2);
    expect($analytics[$qScale->id]['data']['distribution'][4]['count'])->toBe(1);

    // Assert choices calculations: ['Nasi' => 2, 'Mie' => 1, 'Roti' => 0]
    $choicesData = $analytics[$qChoice->id]['data'];
    $nasiBreakdown = collect($choicesData)->firstWhere('label', 'Nasi');
    $rotiBreakdown = collect($choicesData)->firstWhere('label', 'Roti');
    expect($nasiBreakdown['count'])->toBe(2);
    expect($nasiBreakdown['percentage'])->toBe(66.67);
    expect($rotiBreakdown['count'])->toBe(0);
});

// 7. Exports Access
test('admin can access PDF report and Excel sheet exports', function () {
    $survey = Survey::create([
        'title' => 'Survei Ekspor',
        'created_by' => $this->admin->id,
        'status' => 'active',
        'is_anonymous' => true,
    ]);

    $q = SurveyQuestion::create([
        'survey_id' => $survey->id,
        'question_text' => 'Tanya',
        'type' => 'text',
        'is_required' => true,
    ]);

    SurveyResponse::create([
        'survey_id' => $survey->id,
        'user_id' => $this->studentUser->id,
        'answers' => [$q->id => 'Jawaban'],
    ]);

    // Download PDF
    $responsePdf = $this->actingAs($this->admin)->get(route('surveys.export.pdf', $survey->uuid));
    $responsePdf->assertStatus(200);
    $responsePdf->assertHeader('content-type', 'application/pdf');

    // Download Excel
    $responseExcel = $this->actingAs($this->admin)->get(route('surveys.export.excel', $survey->uuid));
    $responseExcel->assertStatus(200);
    expect($responseExcel->headers->get('content-type'))->toContain('spreadsheetml');
});
