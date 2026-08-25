<?php

declare(strict_types=1);

namespace App\Http\Controllers\InteractiveUi;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomLayout;
use App\Models\Room;
use App\Models\Student;
use App\Services\InteractiveUi\ClassroomLayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomLayoutController extends Controller
{
    public function __construct(
        protected ClassroomLayoutService $layoutService
    ) {}

    public function index(): View
    {
        $layouts = ClassroomLayout::with(['classroom', 'room'])->latest()->get();
        $classrooms = Classroom::all();
        $rooms = Room::all();
        $students = Student::take(36)->get();

        return view('interactive-ui.seating-chart-designer', compact('layouts', 'classrooms', 'rooms', 'students'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'room_id' => 'nullable|exists:rooms,id',
            'layout_name' => 'required|string|max:191',
            'grid_rows' => 'required|integer|min:3|max:10',
            'grid_columns' => 'required|integer|min:3|max:10',
            'furniture_positions' => 'nullable|array',
            'layout_id' => 'nullable|exists:classroom_layouts,id',
        ]);

        $layout = $this->layoutService->saveLayout($validated, $validated['layout_id'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Denah kelas berhasil disimpan.',
            'layout' => $layout,
        ]);
    }

    public function assignSeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'classroom_layout_id' => 'required|exists:classroom_layouts,id',
            'student_id' => 'required|exists:students,id',
            'grid_row' => 'required|integer|min:0',
            'grid_column' => 'required|integer|min:0',
            'behavior_note' => 'nullable|string',
        ]);

        $assignment = $this->layoutService->assignSeat(
            (int) $validated['classroom_layout_id'],
            (int) $validated['student_id'],
            (int) $validated['grid_row'],
            (int) $validated['grid_column'],
            $validated['behavior_note'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Posisi tempat duduk siswa berhasil disimpan.',
            'assignment' => $assignment,
        ]);
    }

    public function heatmap(ClassroomLayout $layout): JsonResponse
    {
        $data = $this->layoutService->generateBehaviorHeatmap($layout);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
