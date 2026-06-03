<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Models\ForumThread;
use App\Models\ParentMessage;
use App\Events\ChatMessageSent;
use App\Events\ForumReplyPosted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;

beforeEach(function () {
    $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);

    // Setup dummy broadcasting configuration so auth callbacks are executed
    config([
        'broadcasting.default' => 'pusher',
        'broadcasting.connections.pusher.driver' => 'pusher',
        'broadcasting.connections.pusher.key' => 'local-key',
        'broadcasting.connections.pusher.secret' => 'local-secret',
        'broadcasting.connections.pusher.app_id' => 'sistem-terpadu',
        'broadcasting.connections.pusher.options.host' => 'localhost',
        'broadcasting.connections.pusher.options.port' => 8080,
        'broadcasting.connections.pusher.options.scheme' => 'http',
        
        'broadcasting.connections.reverb.driver' => 'pusher',
        'broadcasting.connections.reverb.key' => 'local-key',
        'broadcasting.connections.reverb.secret' => 'local-secret',
        'broadcasting.connections.reverb.app_id' => 'sistem-terpadu',
        'broadcasting.connections.reverb.options.host' => 'localhost',
        'broadcasting.connections.reverb.options.port' => 8080,
        'broadcasting.connections.reverb.options.scheme' => 'http',
    ]);

    // Manually register channels.php routes under the newly defined broadcaster config
    require base_path('routes/channels.php');
});

test('user channel is strictly authorized for the owner user only', function () {
    $userA = User::factory()->create(['role' => 'parent']);
    $userB = User::factory()->create(['role' => 'parent']);

    // Authorized user listening to their own channel
    $this->actingAs($userA)
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-user.' . $userA->id,
            'socket_id' => '1234.1234',
        ])
        ->assertStatus(200);

    // Unauthorized user listening to someone else's user channel
    $this->actingAs($userB)
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-user.' . $userA->id,
            'socket_id' => '1234.1234',
        ])
        ->assertStatus(403);
});

test('chat channel is authorized only for student parent and homeroom teacher', function () {
    $parent = User::factory()->create(['role' => 'parent']);
    $teacher = User::factory()->create(['role' => 'guru']);
    $other = User::factory()->create(['role' => 'guru']);

    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $student = Student::factory()->create([
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
    ]);

    // Parent should be authorized
    $this->actingAs($parent)
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-chat.' . $student->id,
            'socket_id' => '1234.1234',
        ])
        ->assertStatus(200);

    // Teacher should be authorized
    $this->actingAs($teacher)
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-chat.' . $student->id,
            'socket_id' => '1234.1234',
        ])
        ->assertStatus(200);

    // Other user should not be authorized
    $this->actingAs($other)
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-chat.' . $student->id,
            'socket_id' => '1234.1234',
        ])
        ->assertStatus(403);
});

test('sending chat message dispatches ChatMessageSent event broadcast', function () {
    Event::fake([ChatMessageSent::class]);

    $parent = User::factory()->create(['role' => 'parent']);
    $teacher = User::factory()->create(['role' => 'guru']);
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $student = Student::factory()->create([
        'parent_id' => $parent->id,
        'classroom_id' => $classroom->id,
    ]);

    $this->actingAs($parent)
        ->postJson(route('parent-portal.messages.send'), [
            'student_id' => $student->id,
            'message' => 'Halo Wali Kelas, apa kabar?',
        ])
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    Event::assertDispatched(ChatMessageSent::class, function ($event) use ($student) {
        return $event->message->message === 'Halo Wali Kelas, apa kabar?' &&
               (int) $event->message->student_id === (int) $student->id;
    });
});

test('posting forum reply dispatches ForumReplyPosted event broadcast', function () {
    Event::fake([ForumReplyPosted::class]);

    $teacher = User::factory()->create(['role' => 'guru']);

    // Create a forum thread (model events will run and generate UUID)
    $thread = ForumThread::create([
        'title' => 'Diskusi Belajar Laravel',
        'body' => 'Bagaimana cara setup Laravel Reverb?',
        'user_id' => $teacher->id,
        'scope' => 'school',
    ]);

    $this->actingAs($teacher)
        ->postJson(route('forum.replies.store', $thread->uuid), [
            'body' => 'Cara setup Reverb sangatlah mudah, cukup jalankan php artisan reverb:install.',
        ])
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    Event::assertDispatched(ForumReplyPosted::class, function ($event) use ($thread) {
        return $event->reply->body === 'Cara setup Reverb sangatlah mudah, cukup jalankan php artisan reverb:install.' &&
               (int) $event->reply->thread_id === (int) $thread->id;
    });
});
