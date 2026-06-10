<?php

use App\Models\MeetingNote;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // Setup roles
    $this->admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->student = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $this->parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);

    // Create a mock meeting note for testing show/edit/update
    $this->draftNote = MeetingNote::create([
        'title' => 'Rapat Kurikulum Khas SMA ISA 1',
        'type' => 'rapat_guru',
        'meeting_date' => Carbon::now()->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
        'location' => 'Ruang Wakakur',
        'created_by' => $this->guru->id,
        'agenda' => '# Agenda 1',
        'discussion' => '## Diskusi 1',
        'decisions' => '### Keputusan 1',
        'attendees' => [$this->guru->id],
        'action_items' => [
            [
                'id' => 1,
                'task' => 'Follow up kurikulum',
                'pic' => 'Pak Guru',
                'deadline' => Carbon::now()->addDays(2)->toDateString(),
                'is_completed' => false,
            ]
        ],
        'status' => 'draft',
    ]);
});

// =========================================================
// 🔒 AUTHORIZATION / RBAC TESTS
// =========================================================

test('guest is redirected to login when accessing meeting notes', function () {
    $response = $this->get(route('meeting-notes.index'));
    $response->assertRedirect(route('login'));
});

test('students and parents are forbidden from accessing meeting notes', function () {
    $response = $this->actingAs($this->student)->get(route('meeting-notes.index'));
    $response->assertStatus(403);

    $response2 = $this->actingAs($this->parent)->get(route('meeting-notes.index'));
    $response2->assertStatus(403);
});

test('teachers and admins can view meeting notes list', function () {
    $response = $this->actingAs($this->guru)->get(route('meeting-notes.index'));
    $response->assertStatus(200);
    $response->assertViewHas('meetingNotes');

    $response2 = $this->actingAs($this->admin)->get(route('meeting-notes.index'));
    $response2->assertStatus(200);
});

// =========================================================
// 📝 CRUD OPERATIONS & ACTIONS
// =========================================================

test('authorized user can view create meeting note form', function () {
    $response = $this->actingAs($this->guru)->get(route('meeting-notes.create'));
    $response->assertStatus(200);
    $response->assertSee('Buat Notulensi Rapat Baru');
});

test('authorized user can store new meeting note draft', function () {
    $response = $this->actingAs($this->guru)->post(route('meeting-notes.store'), [
        'title' => 'Rapat Program Tahfidz Semester Baru',
        'type' => 'rapat_guru',
        'meeting_date' => Carbon::now()->addDays(1)->toDateString(),
        'start_time' => '08:00',
        'end_time' => '09:30',
        'location' => 'Masjid Sekolah',
        'agenda' => '# Target 5 Juz',
        'discussion' => '* Evaluasi setoran ziyadah semester lalu',
        'decisions' => '* Siswa kelas X ditargetkan hafal 2 juz',
        'attendees' => [$this->guru->id],
        'action_items' => [
            [
                'task' => 'Siapkan kartu tahfidz baru',
                'pic' => 'Ustadz Ahmad',
                'deadline' => Carbon::now()->addDays(5)->toDateString(),
            ]
        ],
    ]);

    $response->assertRedirect(route('meeting-notes.index'));
    $this->assertDatabaseHas('meeting_notes', [
        'title' => 'Rapat Program Tahfidz Semester Baru',
        'status' => 'draft',
    ]);
});

test('authorized user can view meeting note details with parsed markdown', function () {
    $response = $this->actingAs($this->guru)->get(route('meeting-notes.show', $this->draftNote->id));
    $response->assertStatus(200);
    $response->assertSee('Rapat Kurikulum Khas SMA ISA 1');
    // Verify markdown headers are parsed to HTML tags
    $response->assertSee('<h1 class="text-lg font-black text-gray-900 mt-4 mb-2 uppercase">Agenda 1</h1>', false);
});

test('authorized user can toggle action item completion status via ajax', function () {
    $response = $this->actingAs($this->guru)->post(route('meeting-notes.action-items.toggle', [
        'meeting_note' => $this->draftNote->id,
        'itemId' => 1
    ]));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'is_completed' => true
    ]);

    // Check database
    $updatedNote = MeetingNote::find($this->draftNote->id);
    $this->assertTrue($updatedNote->action_items[0]['is_completed']);
});

test('creator can edit draft meeting note', function () {
    $response = $this->actingAs($this->guru)->get(route('meeting-notes.edit', $this->draftNote->id));
    $response->assertStatus(200);
    $response->assertSee('Edit Notulensi Rapat');
});

test('authorized user can finalize draft meeting note', function () {
    $response = $this->actingAs($this->guru)->post(route('meeting-notes.finalize', $this->draftNote->id));
    $response->assertRedirect(route('meeting-notes.show', $this->draftNote->id));

    $updatedNote = MeetingNote::find($this->draftNote->id);
    $this->assertEquals('finalized', $updatedNote->status);
});

test('finalized meeting note cannot be edited or updated', function () {
    $this->draftNote->update(['status' => 'finalized']);

    // Attempt to view edit form
    $response = $this->actingAs($this->guru)->get(route('meeting-notes.edit', $this->draftNote->id));
    $response->assertRedirect(route('meeting-notes.show', $this->draftNote->id));

    // Attempt to update
    $response2 = $this->actingAs($this->guru)->put(route('meeting-notes.update', $this->draftNote->id), [
        'title' => 'Updated Title But It Should Fail',
        'type' => 'rapat_guru',
        'meeting_date' => Carbon::now()->toDateString(),
        'start_time' => '10:00',
    ]);
    $response2->assertRedirect(route('meeting-notes.show', $this->draftNote->id));
    
    // Check title was NOT updated
    $note = MeetingNote::find($this->draftNote->id);
    $this->assertEquals('Rapat Kurikulum Khas SMA ISA 1', $note->title);
});

test('authorized user can download meeting note PDF', function () {
    $response = $this->actingAs($this->guru)->get(route('meeting-notes.pdf', $this->draftNote->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('authorized user can access action item tracker page', function () {
    $response = $this->actingAs($this->guru)->get(route('meeting-notes.tracker'));
    $response->assertStatus(200);
    $response->assertViewHas('paginatedItems');
});
