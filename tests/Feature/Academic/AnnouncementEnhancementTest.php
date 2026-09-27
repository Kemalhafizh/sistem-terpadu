<?php

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    // Create users with appropriate roles
    $this->superadmin = User::factory()->create(['role' => 'superadmin', 'must_change_password' => false]);
    $this->admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $this->humas = User::factory()->create(['role' => 'waka_humas', 'must_change_password' => false]);
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);

    // Create classroom, student and parent
    $this->classroom = Classroom::factory()->create(['name' => 'XII MIPA 1']);

    $this->studentUser = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'status' => 'active',
    ]);

    $this->parentUser = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    $this->student->update(['parent_id' => $this->parentUser->id]);

    // Create baseline announcement
    $this->announcement = Announcement::create([
        'title' => 'Pengumuman Penting Khas SMA ISA 1',
        'content' => 'Isi pengumuman yang sangat penting dan rahasia.',
        'priority' => 'normal',
        'category' => 'umum',
        'is_pinned' => false,
        'require_acknowledgement' => false,
        'created_by' => $this->humas->id,
        'is_active' => true,
    ]);
});

// =========================================================
// 🔒 RBAC / Authorization Tests
// =========================================================

test('guest cannot access announcements index', function () {
    $response = $this->get(route('announcements.index'));
    $response->assertRedirect(route('login'));
});

test('teachers cannot access announcements index or creation form', function () {
    $response = $this->actingAs($this->guru)->get(route('announcements.index'));
    $response->assertStatus(403);

    $response2 = $this->actingAs($this->guru)->get(route('announcements.create'));
    $response2->assertStatus(403);
});

test('humas and admins can access creation form', function () {
    $response = $this->actingAs($this->humas)->get(route('announcements.create'));
    $response->assertStatus(200);

    $response2 = $this->actingAs($this->admin)->get(route('announcements.create'));
    $response2->assertStatus(200);
});

// =========================================================
// 📣 Rich Announcement CRUD & Custom Features
// =========================================================

test('humas can create rich announcement with attachment and pinning enabled', function () {
    $image = UploadedFile::fake()->image('poster.jpg', 800, 600);
    $doc = UploadedFile::fake()->create('panduan.pdf', 1024, 'application/pdf');

    $response = $this->actingAs($this->humas)->post(route('announcements.store'), [
        'title' => 'Rich Announcement Baru',
        'content' => 'Pengumuman dengan lampiran PDF dan prioritas tinggi.',
        'image' => $image,
        'attachment' => $doc,
        'priority' => 'urgent',
        'category' => 'akademik',
        'target_role' => 'student',
        'target_classroom_id' => $this->classroom->id,
        'is_pinned' => '1',
        'require_acknowledgement' => '1',
    ]);

    $response->assertRedirect(route('announcements.index'));

    $announcement = Announcement::where('title', 'Rich Announcement Baru')->first();
    $this->assertNotNull($announcement);
    $this->assertEquals('urgent', $announcement->priority);
    $this->assertEquals('akademik', $announcement->category);
    $this->assertTrue($announcement->is_pinned);
    $this->assertTrue($announcement->require_acknowledgement);
    $this->assertNotNull($announcement->image_path);
    $this->assertNotNull($announcement->attachment_path);

    Storage::disk('public')->assertExists($announcement->image_path);
    Storage::disk('public')->assertExists($announcement->attachment_path);
});

test('humas can update announcement metadata and change files', function () {
    $image = UploadedFile::fake()->image('new_poster.png');

    $response = $this->actingAs($this->humas)->put(route('announcements.update', $this->announcement), [
        'title' => 'Updated Title',
        'content' => 'Updated content here.',
        'priority' => 'low',
        'category' => 'keislaman',
        'image' => $image,
        'is_pinned' => '1',
    ]);

    $response->assertRedirect(route('announcements.index'));

    $this->announcement->refresh();
    $this->assertEquals('Updated Title', $this->announcement->title);
    $this->assertEquals('keislaman', $this->announcement->category);
    $this->assertEquals('low', $this->announcement->priority);
    $this->assertTrue($this->announcement->is_pinned);
    $this->assertNotNull($this->announcement->image_path);
    Storage::disk('public')->assertExists($this->announcement->image_path);
});

test('authorized users can toggle pinned status', function () {
    $this->assertFalse($this->announcement->is_pinned);

    $response = $this->actingAs($this->humas)->post(route('announcements.pin', $this->announcement));
    $response->assertStatus(302);

    $this->announcement->refresh();
    $this->assertTrue($this->announcement->is_pinned);

    $response2 = $this->actingAs($this->admin)->post(route('announcements.pin', $this->announcement));
    $response2->assertStatus(302);

    $this->announcement->refresh();
    $this->assertFalse($this->announcement->is_pinned);
});

// =========================================================
// 👁️ Read Tracking & Acknowledgement Tests
// =========================================================

test('viewing announcement details increments unique read count and logs read receipt', function () {
    $this->assertEquals(0, $this->announcement->read_count);
    $this->assertFalse($this->announcement->isReadBy($this->studentUser->id));

    // Student views the announcement
    $response = $this->actingAs($this->studentUser)->get(route('announcements.show', $this->announcement));
    $response->assertStatus(200);

    $this->announcement->refresh();
    $this->assertEquals(1, $this->announcement->read_count);
    $this->assertTrue($this->announcement->isReadBy($this->studentUser->id));

    // Student views again (should not increment read count further)
    $response2 = $this->actingAs($this->studentUser)->get(route('announcements.show', $this->announcement));
    $response2->assertStatus(200);

    $this->announcement->refresh();
    $this->assertEquals(1, $this->announcement->read_count);
});

test('students can acknowledge reading announcement when required', function () {
    $this->announcement->update(['require_acknowledgement' => true]);

    $this->assertFalse($this->announcement->isAcknowledgedBy($this->studentUser->id));

    // Acknowledge the announcement
    $response = $this->actingAs($this->studentUser)->post(route('announcements.acknowledge', $this->announcement));
    $response->assertStatus(302);

    $this->announcement->refresh();
    $this->assertTrue($this->announcement->isAcknowledgedBy($this->studentUser->id));
});

test('creator/admin can view read receipts dashboard and stats', function () {
    // Generate some reads
    $this->announcement->update([
        'target_role' => 'student',
        'target_classroom_id' => $this->classroom->id,
        'require_acknowledgement' => true,
    ]);

    // Student reads
    $this->actingAs($this->studentUser)->get(route('announcements.show', $this->announcement));
    // Student acknowledges
    $this->actingAs($this->studentUser)->post(route('announcements.acknowledge', $this->announcement));

    // Admin views receipts dashboard
    $response = $this->actingAs($this->admin)->get(route('announcements.receipts', $this->announcement));
    $response->assertStatus(200);
    $response->assertViewHas('totalTarget', 1);
    $response->assertViewHas('totalRead', 1);
    $response->assertViewHas('totalAcknowledged', 1);
});
