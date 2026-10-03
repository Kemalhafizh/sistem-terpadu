<?php

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

/*
 * Who sees an announcement, decided once in Announcement::visibleTo() for
 * the web and the app; opening and confirming one is recorded either way.
 */

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $this->classA = Classroom::factory()->create(['name' => 'X-1']);
    $this->classB = Classroom::factory()->create(['name' => 'X-2']);
});

function announce(array $attributes = []): Announcement
{
    return Announcement::create([
        'title' => $attributes['title'] ?? 'Pengumuman '.Str::random(5),
        'content' => '<p>Isi pengumuman.</p>',
        'category' => 'umum',
        'priority' => 'normal',
        'is_active' => true,
        'created_by' => test()->admin->id,
        ...$attributes,
    ]);
}

function titlesFor(User $user): array
{
    Sanctum::actingAs($user);

    return collect(test()->getJson('/api/v1/announcements')->assertOk()->json('data'))->pluck('title')->all();
}

it('shows "Guru & Staf" announcements to every staff role', function () {
    announce(['title' => 'Rapat staf', 'target_role' => 'guru']);
    announce(['title' => 'Untuk siswa', 'target_role' => 'student']);

    foreach (['guru', 'staf_tu', 'bk', 'wali_kelas', 'waka_kurikulum'] as $role) {
        expect(titlesFor(User::factory()->create(['role' => $role, 'must_change_password' => false])))
            ->toBe(['Rapat staf']);
    }
});

it('matches every spelling of the student and parent roles', function () {
    announce(['title' => 'Untuk siswa', 'target_role' => 'student']);
    announce(['title' => 'Untuk wali', 'target_role' => 'parent']);

    $siswa = User::factory()->create(['role' => 'siswa', 'must_change_password' => false]);
    Student::factory()->create(['user_id' => $siswa->id, 'classroom_id' => $this->classA->id]);
    $orangTua = User::factory()->create(['role' => 'orang_tua', 'must_change_password' => false]);

    expect(titlesFor($siswa))->toBe(['Untuk siswa'])
        ->and(titlesFor($orangTua))->toBe(['Untuk wali']);
});

it('limits a class announcement to that class and the parents of any child in it', function () {
    announce(['title' => 'Kelas X-2 saja', 'target_classroom_id' => $this->classB->id]);
    $inA = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    Student::factory()->create(['user_id' => $inA->id, 'classroom_id' => $this->classA->id]);
    $parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    // Second child is in X-2: the old dashboard only looked at the first.
    Student::factory()->create(['parent_id' => $parent->id, 'classroom_id' => $this->classA->id]);
    Student::factory()->create(['parent_id' => $parent->id, 'classroom_id' => $this->classB->id]);

    expect(titlesFor($inA))->toBe([])
        ->and(titlesFor($parent))->toBe(['Kelas X-2 saja']);

    Sanctum::actingAs($inA);
    $hidden = Announcement::where('title', 'Kelas X-2 saja')->value('id');
    $this->getJson("/api/v1/announcements/{$hidden}")->assertNotFound();
});

it('drops expired announcements and puts pinned ones first', function () {
    announce(['title' => 'Lama', 'expires_at' => now()->subDay()]);
    announce(['title' => 'Biasa']);
    announce(['title' => 'Penting', 'is_pinned' => true])->forceFill(['created_at' => now()->subDays(3)])->save();

    expect(titlesFor(User::factory()->create(['role' => 'guru', 'must_change_password' => false])))
        ->toBe(['Penting', 'Biasa']);
});

it('records a read once and an acknowledgement when one is required', function () {
    $guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $plain = announce(['title' => 'Info']);
    $required = announce(['title' => 'Wajib dibaca', 'require_acknowledgement' => true]);
    Sanctum::actingAs($guru);

    $this->getJson("/api/v1/announcements/{$plain->id}")->assertOk()->assertJsonPath('data.is_read', true);
    $this->getJson("/api/v1/announcements/{$plain->id}")->assertOk();
    expect($plain->fresh()->read_count)->toBe(1);

    $this->postJson("/api/v1/announcements/{$plain->id}/acknowledge")->assertStatus(422);
    $this->postJson("/api/v1/announcements/{$required->id}/acknowledge")->assertOk()
        ->assertJsonPath('data.require_acknowledgement', true);

    expect($required->isAcknowledgedBy($guru->id))->toBeTrue()
        ->and($required->fresh()->read_count)->toBe(1);

    $list = collect($this->getJson('/api/v1/announcements')->json('data'))->keyBy('title');
    expect($list['Wajib dibaca']['acknowledged_at'])->not->toBeNull()
        ->and($list['Info']['is_read'])->toBeTrue()
        ->and($list['Info']['audience'])->toBe('Semua');
});

it('no longer lets the web page open an announcement meant for others', function () {
    $staffOnly = announce(['title' => 'Rapat staf', 'target_role' => 'guru']);
    $siswa = User::factory()->create(['role' => 'student', 'must_change_password' => false]);

    $this->actingAs($siswa)->get(route('announcements.show', $staffOnly))->assertNotFound();
});
