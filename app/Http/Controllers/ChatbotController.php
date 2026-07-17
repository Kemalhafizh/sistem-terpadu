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

    /**
     * Admin Dashboard: statistics and summary overview.
     */
    public function adminDashboard()
    {
        $analytics = $this->chatbotService->getAnalytics();
        $recentChats = ChatbotConversation::with('user')->latest()->limit(5)->get();
        $faqsCount = ChatbotFaq::count();
        return view('admin.chatbot.dashboard', compact('analytics', 'recentChats', 'faqsCount'));
    }

    /**
     * View conversation history.
     */
    public function conversationHistory()
    {
        $conversations = ChatbotConversation::with('user')->latest()->paginate(15);
        return view('admin.chatbot.conversations', compact('conversations'));
    }

    /**
     * View details of an individual conversation.
     */
    public function showConversation($id)
    {
        $conversation = ChatbotConversation::with('user')->findOrFail($id);
        return view('admin.chatbot.conversation-detail', compact('conversation'));
    }

    /**
     * Escalate conversation to a helpdesk ticket.
     */
    public function escalateToTicket($id)
    {
        $conversation = ChatbotConversation::findOrFail($id);
        
        // Generate a support ticket
        $ticket = \App\Models\Ticket::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reporter_id' => $conversation->user_id,
            'title' => 'Eskalasi Chatbot: ' . \Illuminate\Support\Str::limit($conversation->user_message, 50),
            'description' => "Tiket ini dibuat otomatis dari eskalasi percakapan chatbot.\n\n" .
                             "**Pesan Pengguna:**\n{$conversation->user_message}\n\n" .
                             "**Respon Bot:**\n{$conversation->bot_response}",
            'category' => 'it',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $conversation->update(['intent' => 'escalated']);

        return redirect()->route('admin.chatbot.conversations.show', $conversation->id)
            ->with('success', 'Percakapan berhasil dieskalasikan ke tiket #' . $ticket->id);
    }

    /**
     * View analytics charts.
     */
    public function analytics()
    {
        $analytics = $this->chatbotService->getAnalytics();
        return view('admin.chatbot.analytics', compact('analytics'));
    }

    /**
     * View training suggestions from unanswered queries.
     */
    public function trainSuggestions()
    {
        $suggestions = $this->chatbotService->getTrainingSuggestions();
        return view('admin.chatbot.train-suggestions', compact('suggestions'));
    }

    /**
     * Import FAQs mass CSV.
     */
    public function importFaqs(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:txt,csv|max:2048',
        ]);

        $file = $request->file('csv_file');
        
        $imported = 0;
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // Skip header row
            fgetcsv($handle);

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < 3) continue;

                $question = trim($row[0] ?? '');
                $answer = trim($row[1] ?? '');
                $category = trim($row[2] ?? 'umum');
                $keywordsRaw = trim($row[3] ?? '');

                // Pastikan pertanyaan tidak kosong, tidak terlalu panjang (> 255 karakter), dan jawaban tidak kosong
                if (empty($question) || empty($answer) || strlen($question) > 255) {
                    continue;
                }

                $keywords = !empty($keywordsRaw) 
                    ? array_map('trim', explode(',', $keywordsRaw)) 
                    : [];

                // Bersihkan teks jawaban dari HTML script/XSS tags tapi pertahankan format teks dasar
                $cleanAnswer = strip_tags($answer, '<br><p><b><i><strong><ul><li><ol>');

                ChatbotFaq::create([
                    'question' => $question,
                    'answer' => $cleanAnswer,
                    'category' => in_array($category, ['akademik', 'keuangan', 'teknis', 'umum']) ? $category : 'umum',
                    'keywords' => $keywords,
                    'is_active' => true,
                ]);
                $imported++;
            }
            fclose($handle);
        }

        return redirect()->back()->with('success', "Berhasil mengimpor {$imported} FAQ baru!");
    }

    /**
     * Export FAQs mass CSV.
     */
    public function exportFaqs()
    {
        $faqs = ChatbotFaq::all();
        $csvFileName = 'sista_chatbot_faqs_' . now()->format('YmdHis') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Question', 'Answer', 'Category', 'Keywords'];

        $callback = function() use($faqs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($faqs as $faq) {
                $keywords = is_array($faq->keywords) ? implode(', ', $faq->keywords) : '';
                fputcsv($file, [
                    $faq->question,
                    $faq->answer,
                    $faq->category,
                    $keywords
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
