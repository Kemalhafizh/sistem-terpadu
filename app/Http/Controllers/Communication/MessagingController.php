<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Services\Communication\MessagingService;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    /**
     * Display Internal Messaging Split-Panel Console.
     */
    public function index(Request $request, MessagingService $messagingService)
    {
        $userId = auth()->id();
        $conversations = $messagingService->getConversations($userId);

        $activeConversation = null;
        $messages = collect();

        $activeId = $request->query('conversation_id');
        if ($activeId) {
            $activeConversation = $conversations->firstWhere('id', (int) $activeId);
            if ($activeConversation) {
                $messages = $messagingService->getMessages($activeConversation->id, $userId);
            }
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first();
            $messages = $messagingService->getMessages($activeConversation->id, $userId);
        }

        return view('communication.messaging.index', compact('conversations', 'activeConversation', 'messages'));
    }

    /**
     * Display Compose New Message Form.
     */
    public function compose()
    {
        return view('communication.messaging.compose');
    }

    /**
     * Store new conversation (Direct or Group).
     */
    public function store(Request $request, MessagingService $messagingService)
    {
        $request->validate([
            'type' => 'required|in:direct,group',
            'recipient_id' => 'required_if:type,direct|nullable|exists:users,id',
            'participant_ids' => 'required_if:type,group|nullable|array',
            'subject' => 'required_if:type,group|nullable|string|max:200',
            'body' => 'required|string|max:5000',
        ]);

        $userId = auth()->id();

        if ($request->type === 'direct') {
            $conversation = $messagingService->createDirectConversation(
                $userId,
                (int) $request->recipient_id,
                $request->body
            );
        } else {
            $conversation = $messagingService->createGroupConversation(
                $userId,
                $request->subject,
                array_map('intval', $request->participant_ids),
                $request->body
            );
        }

        return redirect()->route('messages.index', ['conversation_id' => $conversation->id])
            ->with('success', 'Percakapan baru berhasil dibuat!');
    }

    /**
     * Send reply message in active conversation.
     */
    public function reply(Request $request, $conversationId, MessagingService $messagingService)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $userId = auth()->id();
        $messagingService->sendMessage($conversationId, $userId, $request->body);

        return back()->with('success', 'Pesan berhasil terkirim!');
    }

    /**
     * Search users for recipient autocomplete.
     */
    public function searchUsers(Request $request, MessagingService $messagingService)
    {
        $query = $request->query('q', '');
        $userId = auth()->id();

        $users = $messagingService->searchUsers($query, $userId);

        return response()->json($users);
    }
}
