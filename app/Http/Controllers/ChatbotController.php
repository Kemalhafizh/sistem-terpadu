<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFaq;
use App\Models\ChatbotConversation;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Handle AJAX message processing request from floating widget.
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $response = $this->chatbotService->processMessage($user, $request->message);

        return response()->json([
            'success' => true,
            'reply' => $response['reply'],
            'intent' => $response['intent'],
            'conversation_id' => $response['conversation_id'],
        ]);
    }

    /**
     * Record thumbs up/down user feedback for the chatbot reply.
     */
    public function feedback(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:chatbot_conversations,id',
            'was_helpful' => 'required|boolean',
        ]);

        $conversation = ChatbotConversation::where('id', $request->conversation_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $conversation->update([
            'was_helpful' => $request->was_helpful,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas feedback Anda!',
        ]);
    }

    /**
     * Admin Dashboard: Manage Chatbot FAQs.
     */
    public function manageFaqs()
    {
        $faqs = ChatbotFaq::latest()->get();
        return view('admin.chatbot.faqs', compact('faqs'));
    }

    /**
     * Store new FAQ record.
     */
    public function storeFaq(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|in:akademik,keuangan,teknis,umum',
            'keywords' => 'nullable|string',
        ]);

        $keywords = $request->keywords 
            ? array_map('trim', explode(',', $request->keywords)) 
            : [];

        ChatbotFaq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category,
            'keywords' => $keywords,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'FAQ baru berhasil ditambahkan!');
    }

    /**
     * Update existing FAQ record.
     */
    public function updateFaq(Request $request, ChatbotFaq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'required|in:akademik,keuangan,teknis,umum',
            'keywords' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $keywords = $request->keywords 
            ? array_map('trim', explode(',', $request->keywords)) 
            : [];

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category,
            'keywords' => $keywords,
            'is_active' => $request->is_active,
        ]);

        return redirect()->back()->with('success', 'FAQ berhasil diperbarui!');
    }

    /**
     * Delete an FAQ record.
     */
    public function destroyFaq(ChatbotFaq $faq)
    {
        $faq->delete();
        return redirect()->back()->with('success', 'FAQ berhasil dihapus!');
    }
}
