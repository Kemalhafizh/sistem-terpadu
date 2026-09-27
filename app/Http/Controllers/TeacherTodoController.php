<?php

namespace App\Http\Controllers;

use App\Models\TeacherTodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherTodoController extends Controller
{
    /**
     * Display the full todo list page with filters and statistics.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! in_array($user->role, ['guru', 'admin', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'staf_tu', 'bk'])) {
            abort(403);
        }

        $query = TeacherTodo::where('user_id', $user->id);

        // Filter by status
        $status = $request->get('status', 'all');
        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'completed') {
            $query->completed();
        } elseif ($status === 'overdue') {
            $query->overdue();
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->get('category'));
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->get('priority'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'due_date');
        if ($sort === 'due_date') {
            $query->orderByRaw('due_date IS NULL ASC')
                ->orderBy('due_date', 'asc')
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END");
        } elseif ($sort === 'priority') {
            $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
                ->orderByRaw('due_date IS NULL ASC')
                ->orderBy('due_date', 'asc');
        } elseif ($sort === 'created') {
            $query->latest();
        } elseif ($sort === 'category') {
            $query->orderBy('category')->orderBy('due_date');
        }

        $todos = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => TeacherTodo::where('user_id', $user->id)->count(),
            'pending' => TeacherTodo::where('user_id', $user->id)->pending()->count(),
            'completed' => TeacherTodo::where('user_id', $user->id)->completed()->count(),
            'overdue' => TeacherTodo::where('user_id', $user->id)->overdue()->count(),
            'due_today' => TeacherTodo::where('user_id', $user->id)->dueToday()->count(),
            'high_priority' => TeacherTodo::where('user_id', $user->id)->pending()->where('priority', 'high')->count(),
        ];

        // Completion rate
        $stats['completion_rate'] = $stats['total'] > 0
            ? round(($stats['completed'] / $stats['total']) * 100)
            : 0;

        return view('todos.index', compact('todos', 'stats', 'status'));
    }

    /**
     * Store a new todo item (supports both standard form and AJAX).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! in_array($user->role, ['guru', 'admin', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'staf_tu', 'bk'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'category' => 'required|in:mengajar,administrasi,rapat,penilaian,lainnya',
        ]);

        $todo = TeacherTodo::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'category' => $validated['category'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Todo berhasil ditambahkan.',
                'todo' => $todo,
            ]);
        }

        return redirect()->route('todos.index')->with('success', 'Todo berhasil ditambahkan.');
    }

    /**
     * Toggle the completion status of a todo item (AJAX-friendly).
     */
    public function toggle(TeacherTodo $todo)
    {
        $user = Auth::user();

        if ($todo->user_id !== $user->id) {
            abort(403);
        }

        $todo->update([
            'is_completed' => ! $todo->is_completed,
            'completed_at' => ! $todo->is_completed ? now() : null,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $todo->is_completed,
                'message' => $todo->is_completed ? 'Todo selesai! ' : 'Todo dikembalikan ke daftar aktif.',
            ]);
        }

        return back()->with('success', $todo->is_completed ? 'Todo selesai!' : 'Todo dikembalikan ke daftar aktif.');
    }

    /**
     * Update an existing todo item.
     */
    public function update(Request $request, TeacherTodo $todo)
    {
        $user = Auth::user();

        if ($todo->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'category' => 'required|in:mengajar,administrasi,rapat,penilaian,lainnya',
        ]);

        $todo->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'category' => $validated['category'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Todo berhasil diperbarui.',
                'todo' => $todo->fresh(),
            ]);
        }

        return redirect()->route('todos.index')->with('success', 'Todo berhasil diperbarui.');
    }

    /**
     * Delete a todo item.
     */
    public function destroy(TeacherTodo $todo)
    {
        $user = Auth::user();

        if ($todo->user_id !== $user->id) {
            abort(403);
        }

        $todo->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Todo berhasil dihapus.',
            ]);
        }

        return redirect()->route('todos.index')->with('success', 'Todo berhasil dihapus.');
    }

    /**
     * API: Get today's tasks for the dashboard widget.
     */
    public function todayTasks()
    {
        $user = Auth::user();

        if (! in_array($user->role, ['guru', 'admin', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'staf_tu', 'bk'])) {
            abort(403);
        }

        $todayTodos = TeacherTodo::where('user_id', $user->id)
            ->pending()
            ->where(function ($q) {
                $q->whereDate('due_date', now()->toDateString())
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('due_date')
                            ->whereDate('due_date', '<', now()->toDateString());
                    });
            })
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'todos' => $todayTodos,
        ]);
    }
}
