<?php

/** @var TestCase|stdClass $this */

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ElearningClass;
use App\Models\User;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\ForumReaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
    // Setup basic actors
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

// 1. Aksesibilitas Halaman (Page Accessibility)
test('guest is redirected to login when accessing forum routes', function () {
    $this->get(route('forum.index'))->assertRedirect(route('login'));
    $this->get(route('forum.create'))->assertRedirect(route('login'));
});

test('authenticated user can view forum index', function () {
    $this->actingAs($this->studentUser)->get(route('forum.index'))->assertStatus(200);
    $this->actingAs($this->guru)->get(route('forum.index'))->assertStatus(200);
});

// 2. Pembuatan Utas & Validasi (Thread Creation & Validation)
test('teacher can create a thread in school scope with attachment', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('document.pdf', 500); // 500 KB

    $response = $this->actingAs($this->guru)->post(route('forum.threads.store'), [
        'title' => 'Diskusi Belajar Mandiri',
        'body' => 'Mari berdiskusi tentang cara belajar mandiri yang efektif.',
        'scope' => 'school',
        'attachment' => $file,
    ]);

    $response->assertRedirect();
    
    $thread = ForumThread::first();
    expect($thread)->not->toBeNull();
    expect($thread->title)->toBe('Diskusi Belajar Mandiri');
    expect($thread->scope)->toBe('school');
    expect($thread->attachment_path)->not->toBeNull();

    Storage::disk('public')->assertExists($thread->attachment_path);
});

test('student can create a thread in class scope for their own classroom', function () {
    $response = $this->actingAs($this->studentUser)->post(route('forum.threads.store'), [
        'title' => 'Pertanyaan PR Matematika',
        'body' => 'Ada yang bisa bantu nomor 5?',
        'scope' => 'class',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('forum_threads', [
        'title' => 'Pertanyaan PR Matematika',
        'scope' => 'class',
        'classroom_id' => $this->classroom->id,
    ]);
});

test('thread creation validation works as expected', function () {
    $response = $this->actingAs($this->studentUser)->post(route('forum.threads.store'), [
        'title' => '',
        'body' => '',
        'scope' => 'invalid_scope',
    ]);

    $response->assertSessionHasErrors(['title', 'body', 'scope']);
});

test('parent is unauthorized to create thread', function () {
    $response = $this->actingAs($this->parentUser)->post(route('forum.threads.store'), [
        'title' => 'Diskusi Orang Tua',
        'body' => 'Bagaimana tanggapan bapak ibu?',
        'scope' => 'school',
    ]);

    $response->assertStatus(403);
});

// 3. Balasan Bersarang (Nested Replies)
test('user can submit replies and nested replies, automatically updating thread stats', function () {
    $thread = ForumThread::factory()->create([
        'title' => 'Thread Diskusi',
        'body' => 'Isi diskusi',
        'user_id' => $this->guru->id,
        'scope' => 'school',
        'last_reply_at' => now(),
    ])->fresh();

    expect($thread->reply_count)->toBe(0);

    // Level 0 Reply
    $response1 = $this->actingAs($this->studentUser)->post(route('forum.replies.store', $thread->uuid), [
        'body' => 'Ini jawaban pertama saya.',
    ]);
    $response1->assertRedirect();

    $thread->refresh();
    expect($thread->reply_count)->toBe(1);

    $reply0 = ForumReply::where('thread_id', $thread->id)->first();
    expect($reply0)->not->toBeNull();
    expect($reply0->parent_id)->toBeNull();

    // Level 1 Reply (Nested under Level 0)
    $response2 = $this->actingAs($this->guru)->post(route('forum.replies.store', $thread->uuid), [
        'body' => 'Terima kasih atas jawaban kamu!',
        'parent_id' => $reply0->id,
    ]);
    $response2->assertRedirect();

    $thread->refresh();
    expect($thread->reply_count)->toBe(2);

    $reply1 = ForumReply::where('parent_id', $reply0->id)->first();
    expect($reply1)->not->toBeNull();
    expect($reply1->body)->toBe('Terima kasih atas jawaban kamu!');
});

test('locked thread blocks student replies but allows teacher replies', function () {
    $thread = ForumThread::factory()->create([
        'title' => 'Diskusi Terkunci',
        'body' => 'Isi diskusi',
        'user_id' => $this->guru->id,
        'scope' => 'school',
        'is_locked' => true,
        'last_reply_at' => now(),
    ]);

    // Student should be blocked
    $responseStudent = $this->actingAs($this->studentUser)->post(route('forum.replies.store', $thread->uuid), [
        'body' => 'Balasan siswa di thread terkunci.',
    ]);
    $responseStudent->assertStatus(403);

    // Teacher should be allowed
    $responseTeacher = $this->actingAs($this->guru)->post(route('forum.replies.store', $thread->uuid), [
        'body' => 'Balasan guru di thread terkunci.',
    ]);
    $responseTeacher->assertRedirect();
});

// 4. Sistem Reaksi Polymorphic (Polymorphic Reaction System)
test('user can toggle reaction on thread or reply, parent is blocked', function () {
    $thread = ForumThread::factory()->create([
        'title' => 'Thread Reaksi',
        'body' => 'Isi diskusi',
        'user_id' => $this->guru->id,
        'scope' => 'school',
        'last_reply_at' => now(),
    ]);

    $reply = ForumReply::factory()->create([
        'thread_id' => $thread->id,
        'user_id' => $this->studentUser->id,
        'body' => 'Balasan pertama',
    ]);

    // Student reacts to Thread (AJAX)
    $response = $this->actingAs($this->studentUser)->postJson(route('forum.react'), [
        'reactable_type' => 'thread',
        'reactable_id' => $thread->id,
        'type' => 'like',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'success',
            'action' => 'added',
            'likes' => 1,
        ]);

    $this->assertDatabaseHas('forum_reactions', [
        'user_id' => $this->studentUser->id,
        'reactable_type' => ForumThread::class,
        'reactable_id' => $thread->id,
        'type' => 'like',
    ]);

    // Student reacts again (Toggle Off)
    $responseToggle = $this->actingAs($this->studentUser)->postJson(route('forum.react'), [
        'reactable_type' => 'thread',
        'reactable_id' => $thread->id,
        'type' => 'like',
    ]);

    $responseToggle->assertStatus(200)
        ->assertJsonFragment([
            'action' => 'removed',
            'likes' => 0,
        ]);

    $this->assertDatabaseMissing('forum_reactions', [
        'user_id' => $this->studentUser->id,
        'reactable_type' => ForumThread::class,
        'reactable_id' => $thread->id,
        'type' => 'like',
    ]);

    // Parent is blocked from reacting
    $responseParent = $this->actingAs($this->parentUser)->postJson(route('forum.react'), [
        'reactable_type' => 'thread',
        'reactable_id' => $thread->id,
        'type' => 'like',
    ]);

    $responseParent->assertStatus(403);
});

// 5. Kebijakan Keamanan RBAC (RBAC Security Policy)
test('student and parent can only see threads within child/own classroom scope', function () {
    $otherClassroom = Classroom::factory()->create(['academic_year' => $this->academicYear->year]);

    $schoolThread = ForumThread::factory()->create([
        'title' => 'Thread Sekolah',
        'body' => 'Pengumuman sekolah',
        'user_id' => $this->admin->id,
        'scope' => 'school',
        'last_reply_at' => now(),
    ]);

    $ownClassThread = ForumThread::factory()->create([
        'title' => 'Thread Kelas Kita',
        'body' => 'Isi kelas kita',
        'user_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'scope' => 'class',
        'last_reply_at' => now(),
    ]);

    $otherClassThread = ForumThread::factory()->create([
        'title' => 'Thread Kelas Lain',
        'body' => 'Isi kelas lain',
        'user_id' => $this->guru->id,
        'classroom_id' => $otherClassroom->id,
        'scope' => 'class',
        'last_reply_at' => now(),
    ]);

    // Student index access filter: should see school and ownClass, but not otherClass
    $responseStudent = $this->actingAs($this->studentUser)->get(route('forum.index'));
    $responseStudent->assertStatus(200);
    $responseStudent->assertSee('Thread Sekolah');
    $responseStudent->assertSee('Thread Kelas Kita');
    $responseStudent->assertDontSee('Thread Kelas Lain');

    // Parent index access filter: should see school and child's class, but not otherClass
    $responseParent = $this->actingAs($this->parentUser)->get(route('forum.index'));
    $responseParent->assertStatus(200);
    $responseParent->assertSee('Thread Sekolah');
    $responseParent->assertSee('Thread Kelas Kita');
    $responseParent->assertDontSee('Thread Kelas Lain');

    // Individual view checks (Policy check)
    $this->actingAs($this->studentUser)->get(route('forum.show', $schoolThread->uuid))->assertStatus(200);
    $this->actingAs($this->studentUser)->get(route('forum.show', $ownClassThread->uuid))->assertStatus(200);
    $this->actingAs($this->studentUser)->get(route('forum.show', $otherClassThread->uuid))->assertStatus(403);
});

test('parent has read-only status and cannot reply or create threads', function () {
    $thread = ForumThread::factory()->create([
        'title' => 'Diskusi Kelas',
        'body' => 'Pengumuman kelas',
        'user_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'scope' => 'class',
        'last_reply_at' => now(),
    ]);

    // Cannot create thread
    $this->actingAs($this->parentUser)->post(route('forum.threads.store'), [
        'title' => 'Diskusi Baru Orang Tua',
        'body' => 'Sesuatu',
        'scope' => 'school',
    ])->assertStatus(403);

    // Cannot reply
    $this->actingAs($this->parentUser)->post(route('forum.replies.store', $thread->uuid), [
        'body' => 'Jawaban orang tua',
    ])->assertStatus(403);
});

// 6. Tindakan Moderasi Guru/Admin (Guru/Admin Moderation Actions)
test('teacher can pin and lock thread, and mark reply as best answer', function () {
    $thread = ForumThread::factory()->create([
        'title' => 'Thread Moderasi',
        'body' => 'Isi diskusi',
        'user_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'scope' => 'class',
        'last_reply_at' => now(),
    ]);

    // Link teacher to classroom as the teacher in charge
    $this->classroom->teacher_id = $this->guru->id;
    $this->classroom->save();

    $reply1 = ForumReply::factory()->create([
        'thread_id' => $thread->id,
        'user_id' => $this->studentUser->id,
        'body' => 'Balasan Pertama',
    ]);

    $reply2 = ForumReply::factory()->create([
        'thread_id' => $thread->id,
        'user_id' => $this->studentUser->id,
        'body' => 'Balasan Kedua',
    ]);

    // Pin Thread
    $this->actingAs($this->guru)->post(route('forum.threads.pin', $thread->uuid))->assertRedirect();
    $thread->refresh();
    expect($thread->is_pinned)->toBeTrue();

    // Lock Thread
    $this->actingAs($this->guru)->post(route('forum.threads.lock', $thread->uuid))->assertRedirect();
    $thread->refresh();
    expect($thread->is_locked)->toBeTrue();

    // Mark Best Answer
    $reply1->update(['is_best_answer' => true]);

    $this->actingAs($this->guru)->post(route('forum.replies.best', $reply2->uuid))->assertRedirect();
    
    $reply1->refresh();
    $reply2->refresh();
    expect($reply1->is_best_answer)->toBeFalse();
    expect($reply2->is_best_answer)->toBeTrue();
});

test('authorized users can soft delete threads', function () {
    $thread = ForumThread::factory()->create([
        'title' => 'Thread Hapus',
        'body' => 'Isi diskusi',
        'user_id' => $this->guru->id,
        'scope' => 'school',
        'last_reply_at' => now(),
    ]);

    // Guru (author) can delete their own thread
    $this->actingAs($this->guru)->delete(route('forum.threads.destroy', $thread->uuid))->assertRedirect();
    
    $this->assertSoftDeleted('forum_threads', [
        'id' => $thread->id,
    ]);
});
