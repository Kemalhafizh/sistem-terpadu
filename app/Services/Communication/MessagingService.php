<?php

namespace App\Services\Communication;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MessagingService
{
    /**
     * Create or retrieve direct conversation between 2 users.
     */
    public function createDirectConversation(int $userId, int $recipientId, ?string $initialMessage = null): Conversation
    {
        // Check if existing direct conversation exists
        $existingConversationId = DB::table('conversation_participants as p1')
            ->join('conversation_participants as p2', 'p1.conversation_id', '=', 'p2.conversation_id')
            ->join('conversations as c', 'c.id', '=', 'p1.conversation_id')
            ->where('c.type', 'direct')
            ->where('p1.user_id', $userId)
            ->where('p2.user_id', $recipientId)
            ->value('c.id');

        if ($existingConversationId) {
            $conversation = Conversation::findOrFail($existingConversationId);
            if ($initialMessage) {
                $this->sendMessage($conversation->id, $userId, $initialMessage);
            }

            return $conversation;
        }

        return DB::transaction(function () use ($userId, $recipientId, $initialMessage) {
            $recipient = User::findOrFail($recipientId);
            $conversation = Conversation::create([
                'type' => 'direct',
                'subject' => "Percakapan Langsung: {$recipient->name}",
                'created_by' => $userId,
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'last_read_at' => now(),
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $recipientId,
                'last_read_at' => null,
            ]);

            if ($initialMessage) {
                $this->sendMessage($conversation->id, $userId, $initialMessage);
            }

            return $conversation;
        });
    }

    /**
     * Create a group conversation.
     */
    public function createGroupConversation(int $createdBy, string $subject, array $participantIds, ?string $initialMessage = null): Conversation
    {
        return DB::transaction(function () use ($createdBy, $subject, $participantIds, $initialMessage) {
            $conversation = Conversation::create([
                'type' => 'group',
                'subject' => $subject,
                'created_by' => $createdBy,
            ]);

            $allUserIds = array_unique(array_merge([$createdBy], $participantIds));

            foreach ($allUserIds as $uId) {
                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $uId,
                    'last_read_at' => $uId === $createdBy ? now() : null,
                ]);
            }

            if ($initialMessage) {
                $this->sendMessage($conversation->id, $createdBy, $initialMessage);
            }

            return $conversation;
        });
    }

    /**
     * Send a message in a conversation thread.
     */
    public function sendMessage(
        int $conversationId,
        int $senderId,
        string $body,
        ?string $attachmentPath = null,
        bool $isSystemMessage = false
    ): Message {
        $conversation = Conversation::findOrFail($conversationId);

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'attachment_path' => $attachmentPath,
            'is_system_message' => $isSystemMessage,
        ]);

        // Update sender's last read timestamp and conversation updated_at
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $senderId)
            ->update(['last_read_at' => now()]);

        $conversation->touch();

        return $message;
    }

    /**
     * Get active conversations for a user with unread count and latest message.
     */
    public function getConversations(int $userId): Collection
    {
        $participantRecords = ConversationParticipant::where('user_id', $userId)->pluck('conversation_id');

        return Conversation::with(['latestMessage.sender', 'participants.user'])
            ->whereIn('id', $participantRecords)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($conv) use ($userId) {
                $participant = $conv->participants->where('user_id', $userId)->first();
                $lastReadAt = $participant ? $participant->last_read_at : null;

                $unreadCount = $lastReadAt
                    ? $conv->messages()->where('created_at', '>', $lastReadAt)->where('sender_id', '!=', $userId)->count()
                    : $conv->messages()->where('sender_id', '!=', $userId)->count();

                $conv->unread_count = $unreadCount;

                return $conv;
            });
    }

    /**
     * Get messages in a conversation thread with read receipt update.
     */
    public function getMessages(int $conversationId, int $userId, int $perPage = 50)
    {
        // Verify user is participant
        $isParticipant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();

        if (! $isParticipant) {
            throw new \Exception('Akses ditolak: Anda bukan anggota percakapan ini.');
        }

        // Mark as read
        $this->markAsRead($conversationId, $userId);

        return Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Mark conversation as read for user.
     */
    public function markAsRead(int $conversationId, int $userId): void
    {
        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }

    /**
     * Search users for autocomplete when creating messages.
     */
    public function searchUsers(string $query, int $currentUserId): Collection
    {
        return User::where('id', '!=', $currentUserId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email', 'role')
            ->take(10)
            ->get();
    }
}
