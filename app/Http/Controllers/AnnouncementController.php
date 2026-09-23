<?php

namespace App\Http\Controllers;

use App\Events\CampusAnnouncementEvent;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\User;
use App\Models\AnnouncementRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Announcement::class);
        $announcements = Announcement::with('creator', 'targetClassroom')
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);
        $classrooms = Classroom::all();

        return view('announcements.create', compact('classrooms'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,png,jpg,jpeg',
            'target_role' => 'nullable|string',
            'target_classroom_id' => 'nullable|exists:classrooms,id',
            'priority' => 'required|in:low,normal,urgent',
            'category' => 'required|in:akademik,keuangan,kegiatan,keislaman,umum',
            'is_pinned' => 'nullable|boolean',
            'require_acknowledgement' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('announcements/attachments', 'public');
        }

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'attachment_path' => $attachmentPath,
            'target_role' => $validated['target_role'] ?? null,
            'target_classroom_id' => $validated['target_classroom_id'] ?? null,
            'priority' => $validated['priority'],
            'category' => $validated['category'],
            'is_pinned' => $request->has('is_pinned') || $request->boolean('is_pinned'),
            'require_acknowledgement' => $request->has('require_acknowledgement') || $request->boolean('require_acknowledgement'),
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        // FASE 71: real-time push to every connected mobile app (Home/announcements feed).
        event(new CampusAnnouncementEvent([
            'id' => $announcement->id,
            'title' => $announcement->title,
            'summary' => \Illuminate\Support\Str::limit(strip_tags($announcement->content), 140),
            'category' => $announcement->category,
            'priority' => $announcement->priority,
            'timestamp' => $announcement->created_at->toIso8601String(),
        ]));

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil disebarkan.');
    }

    public function show(Announcement $announcement)
    {
        // View policy check: All authenticated users can view, but let's register the read log
        $user = Auth::user();
        
        $readRecord = $announcement->reads()->where('user_id', $user->id)->first();
        if (!$readRecord) {
            $announcement->reads()->create([
                'user_id' => $user->id,
                'read_at' => now(),
            ]);
            $announcement->increment('read_count');
        }

        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $classrooms = Classroom::all();

        return view('announcements.edit', compact('announcement', 'classrooms'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,png,jpg,jpeg',
            'target_role' => 'nullable|string',
            'target_classroom_id' => 'nullable|exists:classrooms,id',
            'priority' => 'required|in:low,normal,urgent',
            'category' => 'required|in:akademik,keuangan,kegiatan,keislaman,umum',
            'is_pinned' => 'nullable|boolean',
            'require_acknowledgement' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $imagePath = $announcement->image_path;
        if ($request->hasFile('image')) {
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        $attachmentPath = $announcement->attachment_path;
        if ($request->hasFile('attachment')) {
            if ($announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }
            $attachmentPath = $request->file('attachment')->store('announcements/attachments', 'public');
        }

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'attachment_path' => $attachmentPath,
            'target_role' => $validated['target_role'] ?? null,
            'target_classroom_id' => $validated['target_classroom_id'] ?? null,
            'priority' => $validated['priority'],
            'category' => $validated['category'],
            'is_pinned' => $request->has('is_pinned') || $request->boolean('is_pinned'),
            'require_acknowledgement' => $request->has('require_acknowledgement') || $request->boolean('require_acknowledgement'),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);
        
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }
        
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function pin(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        
        $announcement->update([
            'is_pinned' => !$announcement->is_pinned
        ]);

        $message = $announcement->is_pinned ? 'Pengumuman berhasil di-pin di dasbor.' : 'Pin pengumuman berhasil dilepas.';
        return back()->with('success', $message);
    }

    public function acknowledge(Announcement $announcement)
    {
        $user = Auth::user();
        
        $readRecord = $announcement->reads()->where('user_id', $user->id)->first();
        if ($readRecord) {
            $readRecord->update([
                'acknowledged_at' => now(),
            ]);
        } else {
            $announcement->reads()->create([
                'user_id' => $user->id,
                'read_at' => now(),
                'acknowledged_at' => now(),
            ]);
            $announcement->increment('read_count');
        }

        return back()->with('success', 'Terima kasih, Anda telah mengonfirmasi bahwa Anda telah membaca dan memahami pengumuman ini.');
    }

    public function receipts(Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $query = User::query();

        // Target audience logic
        if ($announcement->target_role) {
            $query->where('role', $announcement->target_role);
        } else {
            // Exclude administrative roles if audience is public to avoid noise
            $query->whereIn('role', ['student', 'parent', 'guru', 'staf_tu']);
        }

        if ($announcement->target_classroom_id) {
            $query->where(function($q) use ($announcement) {
                $q->whereHas('student', function($sq) use ($announcement) {
                    $sq->where('classroom_id', $announcement->target_classroom_id);
                })
                ->orWhereHas('children', function($sq) use ($announcement) {
                    $sq->where('classroom_id', $announcement->target_classroom_id);
                });
            });
        }

        $targetUsers = $query->with(['student.classroom', 'children.classroom'])->get();
        $reads = $announcement->reads->keyBy('user_id');

        $totalTarget = $targetUsers->count();
        $totalRead = $targetUsers->filter(fn($u) => isset($reads[$u->id]))->count();
        $totalAcknowledged = $targetUsers->filter(fn($u) => isset($reads[$u->id]) && $reads[$u->id]->acknowledged_at !== null)->count();

        return view('announcements.receipts', compact('announcement', 'targetUsers', 'reads', 'totalTarget', 'totalRead', 'totalAcknowledged'));
    }
}
