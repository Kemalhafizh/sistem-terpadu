<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\FamilyTask;
use Illuminate\Http\Request;

class HomeworkCopilotController extends Controller
{
    public function index()
    {
        // A parent sees their own children's tasks only (Student::visibleTo).
        $tasks = FamilyTask::with('student')->whereHas('student', fn ($q) => $q->visibleTo(request()->user()))->get();

        return view('fase62.homework-copilot.dashboard', compact('tasks'));
    }

    public function showTaskDetails($id)
    {
        $task = $this->visibleTask($id);

        return view('fase62.homework-copilot.task-guide', compact('task'));
    }

    public function markAsReviewed(Request $request, $id)
    {
        $task = $this->visibleTask($id);
        $task->update(['parent_reviewed' => true]);

        return redirect()->back();
    }

    public function submitParentFeedback(Request $request, $id)
    {
        $task = $this->visibleTask($id);
        $task->update(['parent_feedback' => $request->feedback]);

        return redirect()->back();
    }

    public function teacherView()
    {
        $tasks = FamilyTask::with('student')->whereHas('student', fn ($q) => $q->visibleTo(request()->user()))->get();

        return view('fase62.homework-copilot.teacher-view', compact('tasks'));
    }

    /** A task of a student the caller may see, else 404 (looked up by id and owner). */
    private function visibleTask($id): FamilyTask
    {
        return FamilyTask::whereHas('student', fn ($q) => $q->visibleTo(request()->user()))->findOrFail($id);
    }
}
