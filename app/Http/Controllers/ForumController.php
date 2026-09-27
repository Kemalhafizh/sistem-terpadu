<?php

namespace App\Http\Controllers;

use App\Events\ForumReplyPosted;
use App\Models\Classroom;
use App\Models\ElearningClass;
use App\Models\ForumReaction;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $this->authorize('viewAny', ForumThread::class);

        $query = ForumThread::with(['author', 'classroom', 'subject', 'elearningClass'])
            ->withCount('replies');

        // Apply RBAC filters for visibility
        if ($user->role === 'student') {
            $student = $user->student;
            $classroomId = $student?->classroom_id;

            $query->where(function ($q) use ($classroomId) {
                $q->where('scope', 'school')
                    ->orWhere(function ($sq) use ($classroomId) {
                        $sq->where('scope', 'class')->where('classroom_id', $classroomId);
                    })
                    ->orWhere(function ($sq) use ($classroomId) {
                        $sq->where('scope', 'subject')->where(function ($ssq) use ($classroomId) {
                            $ssq->where('classroom_id', $classroomId)
                                ->orWhereHas('elearningClass', function ($ec) use ($classroomId) {
                                    $ec->where('classroom_id', $classroomId);
                                });
                        });
                    });
            });
        } elseif ($user->role === 'parent') {
            $childClassrooms = $user->children()->pluck('classroom_id')->toArray();

            $query->where(function ($q) use ($childClassrooms) {
                $q->where('scope', 'school')
                    ->orWhere(function ($sq) use ($childClassrooms) {
                        $sq->where('scope', 'class')->whereIn('classroom_id', $childClassrooms);
                    })
                    ->orWhere(function ($sq) use ($childClassrooms) {
                        $sq->where('scope', 'subject')->where(function ($ssq) use ($childClassrooms) {
                            $ssq->whereIn('classroom_id', $childClassrooms)
                                ->orWhereHas('elearningClass', function ($ec) use ($childClassrooms) {
                                    $ec->whereIn('classroom_id', $childClassrooms);
                                });
                        });
                    });
            });
        }

        // Apply Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Apply Scope Filter
        if ($request->filled('scope')) {
            $query->where('scope', $request->input('scope'));
        }

        // Apply Category/Subject Filter
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Filter: My Threads
        if ($request->boolean('my_threads')) {
            $query->where('user_id', $user->id);
        }

        // Sort
        if ($request->input('sort') === 'popular') {
            $query->orderBy('reply_count', 'desc');
        } else {
            $query->orderBy('is_pinned', 'desc')
                ->orderBy('last_reply_at', 'desc')
                ->orderBy('created_at', 'desc');
        }

        $threads = $query->paginate(15)->withQueryString();

        // Get options for creating threads
        $classrooms = collect();
        $subjects = collect();
        $elearningClasses = collect();

        if ($user->isAdmin()) {
            $classrooms = Classroom::all();
            $subjects = Subject::all();
            $elearningClasses = ElearningClass::all();
        } elseif ($user->role === 'guru') {
            $classrooms = Classroom::where('teacher_id', $user->id)->get();
            $subjects = $user->subjects;
            $elearningClasses = ElearningClass::where('teacher_id', $user->id)->get();
        } elseif ($user->role === 'student' && $user->student) {
            $classrooms = Classroom::where('id', $user->student->classroom_id)->get();
            $elearningClasses = ElearningClass::where('classroom_id', $user->student->classroom_id)->get();
            // Get subjects corresponding to those elearning classes
            $subjects = Subject::whereIn('id', $elearningClasses->pluck('subject_id'))->get();
        }

        return view('forum.index', compact('threads', 'classrooms', 'subjects', 'elearningClasses'));
    }

    public function show(ForumThread $thread)
    {
        $this->authorize('view', $thread);

        $thread->load(['author', 'reactions', 'classroom', 'subject', 'elearningClass']);
        $thread->loadCount('replies');

        // Paginate top-level replies, order best answer to the top
        $replies = ForumReply::where('thread_id', $thread->id)
            ->whereNull('parent_id')
            ->with([
                'author',
                'reactions',
                'children' => function ($q) {
                    $q->orderBy('created_at', 'asc')->with([
                        'author',
                        'reactions',
                        'children' => function ($sq) {
                            $sq->orderBy('created_at', 'asc')->with(['author', 'reactions']);
                        },
                    ]);
                },
            ])
            ->orderBy('is_best_answer', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('forum.show', compact('thread', 'replies'));
    }

    public function create()
    {
        $user = Auth::user();
        $this->authorize('createThread', ForumThread::class);

        $classrooms = collect();
        $subjects = collect();
        $elearningClasses = collect();

        if ($user->isAdmin()) {
            $classrooms = Classroom::all();
            $subjects = Subject::all();
            $elearningClasses = ElearningClass::all();
        } elseif ($user->role === 'guru') {
            $classrooms = Classroom::where('teacher_id', $user->id)->get();
            $subjects = $user->subjects;
            $elearningClasses = ElearningClass::where('teacher_id', $user->id)->get();
        } elseif ($user->role === 'student' && $user->student) {
            $classrooms = Classroom::where('id', $user->student->classroom_id)->get();
            $elearningClasses = ElearningClass::where('classroom_id', $user->student->classroom_id)->get();
            $subjects = Subject::whereIn('id', $elearningClasses->pluck('subject_id'))->get();
        }

        return view('forum.create', compact('classrooms', 'subjects', 'elearningClasses'));
    }

    public function storeThread(Request $request)
    {
        $this->authorize('createThread', ForumThread::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'scope' => 'required|in:class,subject,school',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'elearning_class_id' => 'nullable|exists:elearning_classes,id',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,png,jpeg,zip,rar|max:10240',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('forum_attachments', 'public');
        }

        $user = Auth::user();

        // Enforce classroom_id for students to their own classroom
        if ($user->role === 'student' && $validated['scope'] === 'class') {
            $validated['classroom_id'] = $user->student->classroom_id;
        }

        $isAnnouncement = $request->boolean('is_announcement') && ($user->isAdmin() || $user->role === 'guru');
        $isPinned = $request->boolean('is_pinned') && ($user->isAdmin() || $user->role === 'guru');

        $thread = ForumThread::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'user_id' => $user->id,
            'classroom_id' => $validated['classroom_id'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
            'elearning_class_id' => $validated['elearning_class_id'] ?? null,
            'scope' => $validated['scope'],
            'is_pinned' => $isPinned,
            'is_announcement' => $isAnnouncement,
            'attachment_path' => $attachmentPath,
            'last_reply_at' => now(),
        ]);

        return redirect()->route('forum.show', $thread->uuid)->with('success', 'Topik diskusi berhasil diterbitkan.');
    }

    public function storeReply(Request $request, ForumThread $thread)
    {
        $this->authorize('reply', $thread);

        $validated = $request->validate([
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:forum_replies,id',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,png,jpeg,zip,rar|max:10240',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('forum_attachments', 'public');
        }

        $reply = ForumReply::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
            'attachment_path' => $attachmentPath,
        ]);

        broadcast(new ForumReplyPosted($reply))->toOthers();

        if ($request->wantsJson()) {
            $reply->load('author');

            return response()->json([
                'success' => true,
                'reply' => [
                    'id' => $reply->id,
                    'uuid' => $reply->uuid,
                    'thread_id' => $reply->thread_id,
                    'user_id' => $reply->user_id,
                    'parent_id' => $reply->parent_id,
                    'body' => $reply->body,
                    'attachment_path' => $reply->attachment_path,
                    'created_at_human' => $reply->created_at->diffForHumans(),
                    'author' => [
                        'name' => $reply->author->name,
                        'role' => $reply->author->role,
                    ],
                ],
            ]);
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function togglePin(ForumThread $thread)
    {
        $this->authorize('moderate', $thread);

        $thread->update([
            'is_pinned' => ! $thread->is_pinned,
        ]);

        $status = $thread->is_pinned ? 'ditempel di atas' : 'dilepas dari atas';

        return back()->with('success', "Diskusi berhasil {$status}.");
    }

    public function toggleLock(ForumThread $thread)
    {
        $this->authorize('moderate', $thread);

        $thread->update([
            'is_locked' => ! $thread->is_locked,
        ]);

        $status = $thread->is_locked ? 'dikunci (tutup diskusi)' : 'dibuka kembali';

        return back()->with('success', "Diskusi berhasil {$status}.");
    }

    public function markBestAnswer(ForumReply $reply)
    {
        $thread = $reply->thread;
        $this->authorize('markBestAnswer', $thread);

        // Reset other best answers on this thread
        ForumReply::where('thread_id', $thread->id)
            ->where('id', '!=', $reply->id)
            ->update(['is_best_answer' => false]);

        $reply->update([
            'is_best_answer' => ! $reply->is_best_answer,
        ]);

        $status = $reply->is_best_answer ? 'ditandai sebagai jawaban terbaik' : 'batal ditandai sebagai jawaban terbaik';

        return back()->with('success', "Balasan berhasil {$status}.");
    }

    public function react(Request $request)
    {
        $validated = $request->validate([
            'reactable_type' => 'required|in:thread,reply',
            'reactable_id' => 'required|integer',
            'type' => 'required|in:like,helpful,insightful',
        ]);

        $modelClass = $validated['reactable_type'] === 'thread' ? ForumThread::class : ForumReply::class;
        $reactable = $modelClass::findOrFail($validated['reactable_id']);

        // Check if user is authorized to react (which is equivalent to viewing the thread)
        $thread = $validated['reactable_type'] === 'thread' ? $reactable : $reactable->thread;
        $this->authorize('react', $thread);

        $reaction = ForumReaction::where([
            'user_id' => Auth::id(),
            'reactable_type' => $modelClass,
            'reactable_id' => $validated['reactable_id'],
            'type' => $validated['type'],
        ])->first();

        if ($reaction) {
            $reaction->delete();
            $status = 'removed';
        } else {
            ForumReaction::create([
                'user_id' => Auth::id(),
                'reactable_type' => $modelClass,
                'reactable_id' => $validated['reactable_id'],
                'type' => $validated['type'],
            ]);
            $status = 'added';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'action' => $status,
                'likes' => $reactable->reactions()->where('type', 'like')->count(),
                'helpful' => $reactable->reactions()->where('type', 'helpful')->count(),
                'insightful' => $reactable->reactions()->where('type', 'insightful')->count(),
            ]);
        }

        return back()->with('success', 'Reaksi diperbarui.');
    }

    public function destroy(ForumThread $thread)
    {
        $this->authorize('deleteThread', $thread);

        if ($thread->attachment_path) {
            Storage::disk('public')->delete($thread->attachment_path);
        }

        $thread->delete();

        return redirect()->route('forum.index')->with('success', 'Topik diskusi berhasil dihapus.');
    }
}
