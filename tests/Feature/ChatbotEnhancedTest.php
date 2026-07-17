<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Billing;
use App\Models\ChatbotConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->studentUser = User::factory()->create(['role' => 'student']);
    $this->parentUser = User::factory()->create(['role' => 'parent']);

    $this->classroom = Classroom::factory()->create();
    $this->studentModel = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'status' => 'active'
    ]);
});

test('only admin and superadmin can access chatbot console dashboards', function () {
    // 1. Admin should succeed (200)
    $response = $this->actingAs($this->admin)->get(route('admin.chatbot.dashboard'));
    $response->assertStatus(200);

    // 2. Superadmin should succeed (200)
    $response = $this->actingAs($this->superadmin)->get(route('admin.chatbot.dashboard'));
    $response->assertStatus(200);

    // 3. Student should fail (403)
    $response = $this->actingAs($this->studentUser)->get(route('admin.chatbot.dashboard'));
    $response->assertStatus(403);

    // 4. Guru should fail (403)
    $response = $this->actingAs($this->guru)->get(route('admin.chatbot.dashboard'));
    $response->assertStatus(403);
});

test('student context-aware exam check displays scheduled exams', function () {
    $exam = Exam::factory()->create([
        'title' => 'Ujian Tengah Semester Fisika',
        'classroom_id' => $this->classroom->id,
        'status' => 'published',
        'start_time' => now()->addDays(5),
    ]);

    $response = $this->actingAs($this->studentUser)->postJson(route('chatbot.send'), [
        'message' => 'kapan ada ujian cbt?'
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('intent', 'context_student_exam');
    $response->assertJsonFragment([
        'success' => true
    ]);
    expect($response->json('reply'))->toContain('Ujian Tengah Semester Fisika');
});

test('parent context-aware billing check displays children unpaid bills', function () {
    // Link student child to parent user
    $this->studentModel->update(['parent_id' => $this->parentUser->id]);

    Billing::factory()->create([
        'student_id' => $this->studentModel->id,
        'amount' => 500000,
        'remaining_balance' => 500000,
        'status' => 'unpaid'
    ]);

    $response = $this->actingAs($this->parentUser)->postJson(route('chatbot.send'), [
        'message' => 'berapa total tunggakan spp anak?'
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('intent', 'context_parent_billing');
    expect($response->json('reply'))->toContain('Rp 500.000');
});

test('chatbot conversations can be escalated to support ticket', function () {
    $conversation = ChatbotConversation::create([
        'user_id' => $this->studentUser->id,
        'user_message' => 'Saya butuh bantuan masalah akun',
        'bot_response' => 'Maaf saya tidak mengerti',
        'intent' => 'unknown',
        'confidence' => 0.0
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.chatbot.conversations.escalate', $conversation->id));

    $response->assertRedirect();
    
    $this->assertDatabaseHas('tickets', [
        'reporter_id' => $this->studentUser->id,
        'category' => 'it',
        'status' => 'open'
    ]);

    // Check if conversation intent is updated
    $conversation->refresh();
    expect($conversation->intent)->toBe('escalated');
});

test('faqs can be exported and imported via csv', function () {
    // 1. Export
    $response = $this->actingAs($this->admin)->get(route('admin.chatbot.faqs.export'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    // 2. Import CSV
    $csvContent = "Question,Answer,Category,Keywords\n";
    $csvContent .= "Bagaimana cara reset password?,Silakan hubungi staf TU,teknis,\"reset, sandi, password\"\n";
    
    $file = UploadedFile::fake()->createWithContent('faqs.csv', $csvContent);

    $response = $this->actingAs($this->admin)->post(route('admin.chatbot.faqs.import'), [
        'csv_file' => $file
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('chatbot_faqs', [
        'question' => 'Bagaimana cara reset password?',
        'answer' => 'Silakan hubungi staf TU',
        'category' => 'teknis'
    ]);
});
