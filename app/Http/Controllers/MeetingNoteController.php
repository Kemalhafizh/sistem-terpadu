<?php

namespace App\Http\Controllers;

use App\Models\MeetingNote;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MeetingNoteController extends Controller
{
    /**
     * Display a listing of the meeting notes.
     */
    public function index(Request $request)
    {
        $query = MeetingNote::with('creator');

        // Search by title or location
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('location', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range (month/year)
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('meeting_date', $request->month)
                ->whereYear('meeting_date', $request->year);
        } elseif ($request->filled('year')) {
            $query->whereYear('meeting_date', $request->year);
        }

        $meetingNotes = $query->orderBy('meeting_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        $types = ['rapat_guru', 'rapat_osis', 'rapat_wali_murid', 'rapat_yayasan', 'rapat_panitia', 'lainnya'];
        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $yearsList = range(now()->year - 2, now()->year + 2);

        return view('meeting-notes.index', compact('meetingNotes', 'types', 'monthsList', 'yearsList'));
    }

    /**
     * Show the form for creating a new meeting note.
     */
    public function create()
    {
        $users = User::whereIn('role', ['admin', 'superadmin', 'kepala_sekolah', 'staf_tu', 'guru'])->orderBy('name')->get();
        $types = ['rapat_guru', 'rapat_osis', 'rapat_wali_murid', 'rapat_yayasan', 'rapat_panitia', 'lainnya'];

        return view('meeting-notes.create', compact('users', 'types'));
    }

    /**
     * Store a newly created meeting note in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:rapat_guru,rapat_osis,rapat_wali_murid,rapat_yayasan,rapat_panitia,lainnya',
            'meeting_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
            'discussion' => 'nullable|string',
            'decisions' => 'nullable|string',
            'attendees' => 'nullable|array',
            'action_items' => 'nullable|array',
            'action_items.*.task' => 'required|string',
            'action_items.*.pic' => 'required|string',
            'action_items.*.deadline' => 'required|date',
        ]);

        // Process action items to generate unique item IDs and default is_completed status
        $actionItems = [];
        if ($request->has('action_items')) {
            $idCounter = 1;
            foreach ($request->action_items as $item) {
                if (! empty($item['task']) && ! empty($item['pic'])) {
                    $actionItems[] = [
                        'id' => $idCounter++,
                        'task' => $item['task'],
                        'pic' => $item['pic'],
                        'deadline' => $item['deadline'],
                        'is_completed' => false,
                    ];
                }
            }
        }

        MeetingNote::create([
            'title' => $request->title,
            'type' => $request->type,
            'meeting_date' => $request->meeting_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'created_by' => auth()->id(),
            'agenda' => $request->agenda,
            'discussion' => $request->discussion,
            'decisions' => $request->decisions,
            'attendees' => $request->attendees ?? [],
            'action_items' => $actionItems,
            'status' => 'draft',
        ]);

        return redirect()->route('meeting-notes.index')->with('success', 'Notulensi rapat berhasil dibuat sebagai Draf.');
    }

    /**
     * Display the specified meeting note.
     */
    public function show($id)
    {
        $meetingNote = MeetingNote::with('creator')->findOrFail($id);

        // Load attendees users
        $attendeeUsers = collect();
        if (! empty($meetingNote->attendees)) {
            $attendeeUsers = User::whereIn('id', $meetingNote->attendees)->orderBy('name')->get();
        }

        // Parse markdown text to clean HTML
        $agendaHtml = self::parseMarkdown($meetingNote->agenda);
        $discussionHtml = self::parseMarkdown($meetingNote->discussion);
        $decisionsHtml = self::parseMarkdown($meetingNote->decisions);

        return view('meeting-notes.show', compact('meetingNote', 'attendeeUsers', 'agendaHtml', 'discussionHtml', 'decisionsHtml'));
    }

    /**
     * Show the form for editing the specified meeting note.
     */
    public function edit($id)
    {
        $meetingNote = MeetingNote::findOrFail($id);

        // RBAC check: only creator or admin/superadmin can edit
        if (auth()->id() !== $meetingNote->created_by && ! in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengubah notulensi ini.');
        }

        // Cannot edit finalized meeting notes
        if ($meetingNote->status === 'finalized') {
            return redirect()->route('meeting-notes.show', $meetingNote->id)
                ->with('error', 'Notulensi yang telah difinalisasi tidak dapat diedit kembali.');
        }

        $users = User::whereIn('role', ['admin', 'superadmin', 'kepala_sekolah', 'staf_tu', 'guru'])->orderBy('name')->get();
        $types = ['rapat_guru', 'rapat_osis', 'rapat_wali_murid', 'rapat_yayasan', 'rapat_panitia', 'lainnya'];

        return view('meeting-notes.edit', compact('meetingNote', 'users', 'types'));
    }

    /**
     * Update the specified meeting note in storage.
     */
    public function update(Request $request, $id)
    {
        $meetingNote = MeetingNote::findOrFail($id);

        if (auth()->id() !== $meetingNote->created_by && ! in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        if ($meetingNote->status === 'finalized') {
            return redirect()->route('meeting-notes.show', $meetingNote->id)
                ->with('error', 'Notulensi yang telah difinalisasi tidak dapat diubah.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:rapat_guru,rapat_osis,rapat_wali_murid,rapat_yayasan,rapat_panitia,lainnya',
            'meeting_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
            'discussion' => 'nullable|string',
            'decisions' => 'nullable|string',
            'attendees' => 'nullable|array',
            'action_items' => 'nullable|array',
            'action_items.*.task' => 'required|string',
            'action_items.*.pic' => 'required|string',
            'action_items.*.deadline' => 'required|date',
        ]);

        // Process action items: keep existing items statuses if they were completed
        $existingItems = $meetingNote->action_items ?? [];
        $existingMap = [];
        foreach ($existingItems as $ei) {
            $existingMap[$ei['task'].'|'.$ei['pic']] = $ei['is_completed'] ?? false;
        }

        $actionItems = [];
        if ($request->has('action_items')) {
            $idCounter = 1;
            foreach ($request->action_items as $item) {
                if (! empty($item['task']) && ! empty($item['pic'])) {
                    $key = $item['task'].'|'.$item['pic'];
                    $isCompleted = isset($item['is_completed']) ? filter_var($item['is_completed'], FILTER_VALIDATE_BOOLEAN) : ($existingMap[$key] ?? false);

                    $actionItems[] = [
                        'id' => $idCounter++,
                        'task' => $item['task'],
                        'pic' => $item['pic'],
                        'deadline' => $item['deadline'],
                        'is_completed' => $isCompleted,
                    ];
                }
            }
        }

        $meetingNote->update([
            'title' => $request->title,
            'type' => $request->type,
            'meeting_date' => $request->meeting_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'agenda' => $request->agenda,
            'discussion' => $request->discussion,
            'decisions' => $request->decisions,
            'attendees' => $request->attendees ?? [],
            'action_items' => $actionItems,
        ]);

        return redirect()->route('meeting-notes.show', $meetingNote->id)->with('success', 'Notulensi rapat berhasil diperbarui.');
    }

    /**
     * Finalize the meeting note (no longer editable).
     */
    public function finalize($id)
    {
        $meetingNote = MeetingNote::findOrFail($id);

        if (auth()->id() !== $meetingNote->created_by && ! in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $meetingNote->update(['status' => 'finalized']);

        return redirect()->route('meeting-notes.show', $meetingNote->id)->with('success', 'Notulensi rapat berhasil difinalisasi dan dikunci dari perubahan.');
    }

    /**
     * Tracker page for Action Items.
     */
    public function actionItemTracker(Request $request)
    {
        $meetings = MeetingNote::whereNotNull('action_items')->orderBy('meeting_date', 'desc')->get();

        $actionItems = collect();
        foreach ($meetings as $meeting) {
            $items = $meeting->action_items ?? [];
            foreach ($items as $item) {
                // Apply search filter
                if ($request->filled('search')) {
                    $searchTerm = strtolower($request->search);
                    if (str_contains(strtolower($item['task']), $searchTerm) === false &&
                        str_contains(strtolower($item['pic']), $searchTerm) === false &&
                        str_contains(strtolower($meeting->title), $searchTerm) === false) {
                        continue;
                    }
                }

                // Apply status filter
                if ($request->filled('status')) {
                    $isCompleted = $request->status === 'completed';
                    if ($item['is_completed'] !== $isCompleted) {
                        continue;
                    }
                }

                $actionItems->push((object) [
                    'meeting_id' => $meeting->id,
                    'meeting_title' => $meeting->title,
                    'meeting_date' => $meeting->meeting_date,
                    'id' => $item['id'],
                    'task' => $item['task'],
                    'pic' => $item['pic'],
                    'deadline' => Carbon::parse($item['deadline']),
                    'is_completed' => $item['is_completed'] ?? false,
                ]);
            }
        }

        // Paginate manually
        $page = $request->input('page', 1);
        $perPage = 15;
        $paginatedItems = new LengthAwarePaginator(
            $actionItems->forPage($page, $perPage)->values(),
            $actionItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('meeting-notes.action-tracker', compact('paginatedItems'));
    }

    /**
     * Toggle action item completion status.
     */
    public function toggleActionItem($meetingId, $itemId)
    {
        $meetingNote = MeetingNote::findOrFail($meetingId);

        // Allow teachers and management to toggle tasks
        if (! in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'staf_tu', 'guru'])) {
            abort(403);
        }

        $items = $meetingNote->action_items ?? [];
        $updated = false;

        foreach ($items as &$item) {
            if ($item['id'] == $itemId) {
                $item['is_completed'] = ! ($item['is_completed'] ?? false);
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $meetingNote->update(['action_items' => $items]);

            return response()->json([
                'success' => true,
                'message' => 'Status action item berhasil diperbarui.',
                'is_completed' => $item['is_completed'],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Action item tidak ditemukan.'], 404);
    }

    /**
     * Export Meeting Note to official PDF format.
     */
    public function exportPdf($id)
    {
        $meetingNote = MeetingNote::with('creator')->findOrFail($id);

        $attendeeUsers = collect();
        if (! empty($meetingNote->attendees)) {
            $attendeeUsers = User::whereIn('id', $meetingNote->attendees)->orderBy('name')->get();
        }

        $agendaHtml = self::parseMarkdown($meetingNote->agenda);
        $discussionHtml = self::parseMarkdown($meetingNote->discussion);
        $decisionsHtml = self::parseMarkdown($meetingNote->decisions);

        $pdf = Pdf::loadView('meeting-notes.pdf', compact('meetingNote', 'attendeeUsers', 'agendaHtml', 'discussionHtml', 'decisionsHtml'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Notulensi_Rapat_'.str_replace(' ', '_', $meetingNote->title).'.pdf');
    }

    /**
     * Helper to parse simple markdown syntax into safe HTML.
     */
    private static function parseMarkdown($text)
    {
        if (empty($text)) {
            return '';
        }

        // Escape original content for safety
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Headers
        $text = preg_replace('/^#\s+(.+)$/m', '<h1 class="text-lg font-black text-gray-900 mt-4 mb-2 uppercase">$1</h1>', $text);
        $text = preg_replace('/^##\s+(.+)$/m', '<h2 class="text-base font-bold text-gray-800 mt-3 mb-1.5 uppercase">$1</h2>', $text);
        $text = preg_replace('/^###\s+(.+)$/m', '<h3 class="text-sm font-bold text-gray-700 mt-2 mb-1">$1</h3>', $text);

        // Bold
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);

        // Italics
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

        // Lists (asterisk or hyphen)
        $text = preg_replace('/^\s*[\*\-]\s+(.+)$/m', '<li class="ml-4 list-disc">$1</li>', $text);

        // Line breaks
        $text = nl2br($text);

        return $text;
    }
}
