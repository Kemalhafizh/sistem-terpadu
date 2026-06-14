<?php

use App\Models\User;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\ChatbotFaq;
use App\Models\ChatbotConversation;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    //
});

test('unauthenticated users are blocked from sending chatbot messages', function () {
    $response = $this->postJson(route('chatbot.send'), [
        'message' => 'Halo SISTA!',
    ]);

    $response->assertStatus(401);
});

test('authenticated users can send message and receive chatbot response', function () {
    $user = User::factory()->create(['role' => 'guru']);
    
    // Seed a quick FAQ
    $faq = ChatbotFaq::create([
        'question' => 'Bagaimana aturan seragam sekolah?',
        'answer' => 'Siswa wajib mengenakan seragam OSIS lengkap pada hari Senin.',
        'category' => 'umum',
        'keywords' => ['seragam', 'aturan'],
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->postJson(route('chatbot.send'), [
        'message' => 'Bagaimana aturan seragam sekolah?',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('intent', 'faq');
    $response->assertJsonFragment(['reply' => $faq->answer]);

    // Check database entry
    $this->assertDatabaseHas('chatbot_conversations', [
        'user_id' => $user->id,
        'user_message' => 'Bagaimana aturan seragam sekolah?',
        'intent' => 'faq',
    ]);
});

test('navigation keywords are automatically parsed into target urls', function () {
    $user = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($user)->postJson(route('chatbot.send'), [
        'message' => 'tolong buka absensi dong',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('intent', 'navigate');
    $response->assertSee('/attendances');
});

test('natural language queries are resolved into secure eloquent counts', function () {
    $user = User::factory()->create(['role' => 'guru']);

    // Create 3 active student records
    Student::factory()->count(3)->create(['status' => 'active']);

    $response = $this->actingAs($user)->postJson(route('chatbot.send'), [
        'message' => 'berapa total siswa aktif?',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('intent', 'query_data');
    $response->assertSee('3'); // Assert the count is returned
});

test('users can submit helpful feedback on chatbot conversation', function () {
    $user = User::factory()->create(['role' => 'student']);
    
    $convo = ChatbotConversation::create([
        'user_id' => $user->id,
        'user_message' => 'Tanya SPP',
        'bot_response' => 'SPP lunas.',
        'intent' => 'faq',
    ]);

    $response = $this->actingAs($user)->postJson(route('chatbot.feedback'), [
        'conversation_id' => $convo->id,
        'was_helpful' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    $this->assertEquals(true, $convo->fresh()->was_helpful);
});

test('non-admin users are blocked from admin FAQs page', function () {
    $user = User::factory()->create(['role' => 'guru']);

    $response = $this->actingAs($user)->get(route('admin.chatbot.faqs'));

    $response->assertStatus(403);
});

test('admin can manage chatbot FAQs CRUD operations successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // 1. Create FAQ
    $response = $this->actingAs($admin)->post(route('admin.chatbot.faqs.store'), [
        'question' => 'Jam masuk sekolah?',
        'answer' => 'Jam 07:00 WIB.',
        'category' => 'umum',
        'keywords' => 'jam, masuk, waktu',
    ]);

    $response->assertStatus(302); // Redirect back
    $this->assertDatabaseHas('chatbot_faqs', [
        'question' => 'Jam masuk sekolah?',
        'category' => 'umum',
    ]);

    $faq = ChatbotFaq::first();

    // 2. Update FAQ
    $response = $this->actingAs($admin)->put(route('admin.chatbot.faqs.update', $faq->id), [
        'question' => 'Jam masuk sekolah?',
        'answer' => 'Jam 07:30 WIB.',
        'category' => 'umum',
        'keywords' => 'jam, masuk, terlambat',
        'is_active' => 1,
    ]);

    $response->assertStatus(302);
    $this->assertEquals('Jam 07:30 WIB.', $faq->fresh()->answer);

    // 3. Delete FAQ
    $response = $this->actingAs($admin)->delete(route('admin.chatbot.faqs.destroy', $faq->id));
    $response->assertStatus(302);
    $this->assertDatabaseMissing('chatbot_faqs', ['id' => $faq->id]);
});
