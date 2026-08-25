<?php

declare(strict_types=1);

namespace App\Http\Controllers\InteractiveUi;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Room;
use App\Models\ScheduleBuilderDraft;
use App\Models\Subject;
use App\Models\User;
use App\Services\InteractiveUi\ScheduleBuilderEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleBuilderController extends Controller
{
    public function __construct(
        protected ScheduleBuilderEngineService $builderEngine
    ) {}

    public function index(): View
    {
        $drafts = ScheduleBuilderDraft::with('creator')->latest()->get();
        $classrooms = Classroom::all();
        $subjects = Subject::all();
        $teachers = User::where('role', 'guru')->get();
        $rooms = Room::all();

        return view('interactive-ui.schedule-builder', compact('drafts', 'classrooms', 'subjects', 'teachers', 'rooms'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'schedule_data' => 'required|array',
            'draft_id' => 'nullable|exists:schedule_builder_drafts,id',
        ]);

        $draft = $this->builderEngine->saveDraft(
            $request->user(),
            $validated,
            $validated['draft_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Draft jadwal berhasil disimpan.',
            'draft' => $draft,
        ]);
    }

    public function validateDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_data' => 'required|array',
        ]);

        $conflicts = $this->builderEngine->detectConflicts($validated['schedule_data']);

        return response()->json([
            'success' => true,
            'has_conflicts' => count($conflicts) > 0,
            'conflicts_count' => count($conflicts),
            'conflicts' => $conflicts,
        ]);
    }

    public function publish(ScheduleBuilderDraft $draft): JsonResponse
    {
        $published = $this->builderEngine->publishSchedule($draft);

        return response()->json([
            'success' => $published,
            'message' => $published ? 'Jadwal pelajaran berhasil dipublikasikan ke sistem aktif.' : 'Gagal mempublikasikan jadwal.',
        ]);
    }
}
