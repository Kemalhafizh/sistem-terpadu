<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

/*
 * Reading and clearing notifications from the app is saved on the server,
 * for the account's own notifications only.
 */

function notify(User $user, string $title, bool $read = false): string
{
    $id = (string) Str::uuid();
    $user->notifications()->create([
        'id' => $id,
        'type' => 'App\\Notifications\\EventReminderNotification',
        'data' => ['title' => $title, 'message' => "Isi {$title}"],
        'read_at' => $read ? now() : null,
    ]);

    return $id;
}

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->other = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    Sanctum::actingAs($this->user);
});

it('marks one, then all, as read and counts what is left', function () {
    $first = notify($this->user, 'Satu');
    notify($this->user, 'Dua');
    notify($this->user, 'Tiga', read: true);

    $this->getJson('/api/v1/notifications/unread-count')->assertOk()->assertJsonPath('data.count', 2);
    $this->postJson("/api/v1/notifications/{$first}/read")->assertOk()->assertJsonPath('data.is_read', true);
    $this->getJson('/api/v1/notifications/unread-count')->assertJsonPath('data.count', 1);
    $this->postJson('/api/v1/notifications/read-all')->assertOk()->assertJsonPath('data.updated', 1);
    $this->getJson('/api/v1/notifications/unread-count')->assertJsonPath('data.count', 0);
});

it('deletes a notification for good', function () {
    $id = notify($this->user, 'Hapus aku');

    $this->deleteJson("/api/v1/notifications/{$id}")->assertOk();

    expect(collect($this->getJson('/api/v1/notifications')->json('data'))->pluck('id'))->not->toContain($id);
});

it('cannot touch another account\'s notifications', function () {
    $theirs = notify($this->other, 'Milik orang lain');

    $this->postJson("/api/v1/notifications/{$theirs}/read")->assertNotFound();
    $this->deleteJson("/api/v1/notifications/{$theirs}")->assertNotFound();

    expect($this->other->unreadNotifications()->count())->toBe(1);
});
