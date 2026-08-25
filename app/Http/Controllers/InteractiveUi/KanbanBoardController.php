<?php

declare(strict_types=1);

namespace App\Http\Controllers\InteractiveUi;

use App\Http\Controllers\Controller;
use App\Models\KanbanBoard;
use App\Models\KanbanTask;
use App\Models\User;
use App\Services\InteractiveUi\KanbanManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KanbanBoardController extends Controller
{
    public function __construct(
        protected KanbanManagerService $kanbanService
    ) {}

    public function index(Request $request): View
    {
        $boards = KanbanBoard::with(['owner', 'tasks.assignee'])->latest()->get();
        if ($boards->isEmpty()) {
            $defaultBoard = $this->kanbanService->createBoard($request->user(), 'Papan Tugas Utama Guru & Staf', 'general');
            $boards = collect([$defaultBoard->load('tasks')]);
        }

        $activeBoard = $boards->first();
        $teachers = User::whereIn('role', ['guru', 'admin', 'staff', 'kepala_sekolah'])->get();

        return view('interactive-ui.kanban-board', compact('boards', 'activeBoard', 'teachers'));
    }

    public function storeBoard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'type' => 'required|in:general,teaching,project,admin,school_event',
        ]);

        $board = $this->kanbanService->createBoard($request->user(), $validated['title'], $validated['type']);

        return response()->json([
            'success' => true,
            'message' => 'Papan Kanban baru berhasil dibuat.',
            'board' => $board,
        ]);
    }

    public function storeTask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kanban_board_id' => 'required|exists:kanban_boards,id',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'column_id' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $task = KanbanTask::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kartu tugas berhasil ditambahkan ke papan.',
            'task' => $task->load('assignee'),
        ]);
    }

    public function moveTask(Request $request, KanbanTask $task): JsonResponse
    {
        $validated = $request->validate([
            'column_id' => 'required|string',
            'position_order' => 'required|integer',
        ]);

        $updated = $this->kanbanService->moveTask($task->id, $validated['column_id'], (int) $validated['position_order']);

        return response()->json([
            'success' => true,
            'message' => 'Posisi kartu berhasil diperbarui.',
            'task' => $updated,
        ]);
    }
}
