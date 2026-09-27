<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets with search and filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::with(['reporter', 'assignee'])
            ->forUser($user)
            ->orderBy('created_at', 'desc');

        // Apply dynamic filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Calculate basic counts for user dashboard
        $metrics = [];
        if (in_array($user->role, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            $metrics = [
                'total' => Ticket::count(),
                'open' => Ticket::whereIn('status', ['open', 'in_progress', 'waiting'])->count(),
                'resolved' => Ticket::where('status', 'resolved')->count(),
                'overdue' => Ticket::where('due_date', '<', now())->whereNotIn('status', ['resolved', 'closed'])->count(),
            ];
        } else {
            $metrics = [
                'total' => Ticket::where('reporter_id', $user->id)->count(),
                'open' => Ticket::where('reporter_id', $user->id)->whereIn('status', ['open', 'in_progress', 'waiting'])->count(),
                'resolved' => Ticket::where('reporter_id', $user->id)->where('status', 'resolved')->count(),
                'overdue' => Ticket::where('reporter_id', $user->id)->where('due_date', '<', now())->whereNotIn('status', ['resolved', 'closed'])->count(),
            ];
        }

        return view('helpdesk.index', compact('tickets', 'metrics'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        return view('helpdesk.create');
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:it,sarpras,akademik,keuangan,umum',
            'priority' => 'required|in:low,medium,high,critical',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,zip|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('helpdesk/attachments', 'public');
        }

        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
            'reporter_id' => Auth::id(),
            'attachment_path' => $attachmentPath,
        ]);

        // Send notifications
        // 1. Notify reporter (Self)
        Auth::user()->notify(new TicketCreatedNotification($ticket, 'reporter'));

        // 2. Notify helpdesk staff/admins
        $staff = User::whereIn('role', ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])->get();
        foreach ($staff as $person) {
            $person->notify(new TicketCreatedNotification($ticket, 'staff'));
        }

        return redirect()->route('helpdesk.tickets.show', $ticket->uuid)
            ->with('success', 'Tiket support #'.$ticket->ticket_number.' berhasil diajukan.');
    }

    /**
     * Display the specified ticket with replies.
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $commentsQuery = $ticket->comments()->with('user')->orderBy('created_at', 'asc');

        // Hide internal comments from normal users (students/parents/teachers)
        if (! Auth::user()->can('viewInternalComments', $ticket)) {
            $commentsQuery->where('is_internal', false);
        }

        $comments = $commentsQuery->get();

        // Staf options for assignment dropdown
        $staffList = [];
        if (in_array(Auth::user()->role, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            $staffList = User::whereIn('role', ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])->get();
        }

        return view('helpdesk.show', compact('ticket', 'comments', 'staffList'));
    }

    /**
     * Post a reply comment to the ticket.
     */
    public function comment(Request $request, Ticket $ticket)
    {
        $this->authorize('comment', $ticket);

        $request->validate([
            'body' => 'required|string',
            'is_internal' => 'nullable|boolean',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,zip|max:5120',
        ]);

        $isInternal = $request->boolean('is_internal', false);

        // Safety boundary check
        if ($isInternal) {
            $this->authorize('viewInternalComments', $ticket);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('helpdesk/attachments', 'public');
        }

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $request->body,
            'is_internal' => $isInternal,
            'attachment_path' => $attachmentPath,
        ]);

        // Send updates if it is a public comment
        if (! $isInternal) {
            // If commenter is staff, notify reporter
            if (Auth::id() !== $ticket->reporter_id) {
                $ticket->reporter->notify(new TicketStatusUpdatedNotification($ticket, 'comment_added', [
                    'commenter_name' => Auth::user()->name,
                    'comment_body' => $comment->body,
                ]));
            }

            // If commenter is reporter and ticket has an assignee, notify assignee
            if (Auth::id() === $ticket->reporter_id && $ticket->assignee) {
                $ticket->assignee->notify(new TicketStatusUpdatedNotification($ticket, 'comment_added', [
                    'commenter_name' => Auth::user()->name,
                    'comment_body' => $comment->body,
                ]));
            }
        }

        return redirect()->back()->with('success', 'Respon Anda berhasil ditambahkan.');
    }

    /**
     * Assign a staff member to handle the ticket.
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $request->validate([
            'assignee_id' => 'required|exists:users,id',
        ]);

        $ticket->update([
            'assignee_id' => $request->assignee_id,
            'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);

        // Notify reporter
        $ticket->reporter->notify(new TicketStatusUpdatedNotification($ticket, 'assigned'));

        return redirect()->back()->with('success', 'Petugas penanggung jawab berhasil ditugaskan.');
    }

    /**
     * Resolve the ticket with a resolution note.
     */
    public function resolve(Request $request, Ticket $ticket)
    {
        $this->authorize('resolve', $ticket);

        $request->validate([
            'resolution' => 'required|string',
        ]);

        $ticket->update([
            'resolution' => $request->resolution,
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        // Notify reporter
        $ticket->reporter->notify(new TicketStatusUpdatedNotification($ticket, 'status_changed'));

        return redirect()->back()->with('success', 'Tiket support berhasil ditandai selesai.');
    }

    /**
     * Close the ticket completely.
     */
    public function close(Request $request, Ticket $ticket)
    {
        $this->authorize('close', $ticket);

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // Notify reporter if closed by staff
        if (Auth::id() !== $ticket->reporter_id) {
            $ticket->reporter->notify(new TicketStatusUpdatedNotification($ticket, 'status_changed'));
        }

        return redirect()->back()->with('success', 'Tiket support berhasil ditutup secara permanen.');
    }

    /**
     * Display helpdesk analytics and reports for administrative staff.
     */
    public function dashboard()
    {
        $user = Auth::user();
        if (! in_array($user->role, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            abort(403, 'Akses ditolak.');
        }

        // Metrik utama
        $total = Ticket::count();
        $open = Ticket::where('status', 'open')->count();
        $inProgress = Ticket::where('status', 'in_progress')->count();
        $resolved = Ticket::where('status', 'resolved')->count();
        $closed = Ticket::where('status', 'closed')->count();

        $overdue = Ticket::where('due_date', '<', now())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        // Hitung rata-rata waktu resolusi (dalam jam)
        $resolvedTickets = Ticket::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->get();

        $avgResolutionHours = 0;
        if ($resolvedTickets->count() > 0) {
            $totalHours = 0;
            foreach ($resolvedTickets as $t) {
                $totalHours += $t->created_at->diffInHours($t->resolved_at);
            }
            $avgResolutionHours = round($totalHours / $resolvedTickets->count(), 1);
        }

        // Data chart kategori
        $categoriesData = Ticket::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        // Pastikan semua kategori memiliki nilai awal
        $categories = ['it' => 0, 'sarpras' => 0, 'akademik' => 0, 'keuangan' => 0, 'umum' => 0];
        foreach ($categoriesData as $cat => $count) {
            $categories[$cat] = $count;
        }

        // Tiket kritis (prioritas tinggi / critical dan belum diselesaikan)
        $criticalTickets = Ticket::with(['reporter', 'assignee'])
            ->whereIn('priority', ['high', 'critical'])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        return view('helpdesk.dashboard', compact(
            'total', 'open', 'inProgress', 'resolved', 'closed', 'overdue',
            'avgResolutionHours', 'categories', 'criticalTickets'
        ));
    }
}
