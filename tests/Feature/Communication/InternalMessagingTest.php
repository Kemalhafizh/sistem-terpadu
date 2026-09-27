<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Communication\MessagingService;

beforeEach(function () {
    $this->user1 = User::factory()->create(['role' => 'admin', 'name' => 'Admin Sekolah']);
    $this->user2 = User::factory()->create(['role' => 'guru', 'name' => 'Guru Saintek']);
    $this->messagingService = app(MessagingService::class);
});

test('messaging service creates direct and group conversations', function () {
    $direct = $this->messagingService->createDirectConversation(
        $this->user1->id,
        $this->user2->id,
        'Halo Ustadz, ini pesan direct pertama.'
    );

    expect($direct->type)->toBe('direct');
    expect(Message::where('conversation_id', $direct->id)->count())->toBe(1);

    $group = $this->messagingService->createGroupConversation(
        $this->user1->id,
        'Grup Panitia PAS',
        [$this->user2->id],
        'Selamat datang di grup panitia.'
    );

    expect($group->type)->toBe('group');
    expect($group->subject)->toBe('Grup Panitia PAS');
});

test('user can view messaging console and active message thread', function () {
    $conversation = $this->messagingService->createDirectConversation(
        $this->user1->id,
        $this->user2->id,
        'Pesan pengujian split panel.'
    );

    $response = $this->actingAs($this->user1)->get(route('messages.index', ['conversation_id' => $conversation->id]));
    $response->assertStatus(200);
    $response->assertSee('Pesan pengujian split panel.');
});

test('user can reply to conversation thread', function () {
    $conversation = $this->messagingService->createDirectConversation(
        $this->user1->id,
        $this->user2->id,
        'Pesan awal.'
    );

    $replyResponse = $this->actingAs($this->user2)->post(route('messages.reply', $conversation->id), [
        'body' => 'Balasan dari guru saintek.',
    ]);

    $replyResponse->assertRedirect();
    expect(Message::where('conversation_id', $conversation->id)->count())->toBe(2);
});

test('user can compose new conversation via controller', function () {
    $composeView = $this->actingAs($this->user1)->get(route('messages.compose'));
    $composeView->assertStatus(200);

    $storeResponse = $this->actingAs($this->user1)->post(route('messages.store'), [
        'type' => 'direct',
        'recipient_id' => $this->user2->id,
        'body' => 'Pesan via controller store.',
    ]);

    $storeResponse->assertRedirect();
    expect(Conversation::where('created_by', $this->user1->id)->exists())->toBeTrue();
});

test('user search API returns filtered results', function () {
    $searchResponse = $this->actingAs($this->user1)->get(route('messages.search_users', ['q' => 'Guru']));
    $searchResponse->assertStatus(200);
    $searchResponse->assertJsonFragment(['name' => 'Guru Saintek']);
});
