<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\FamilyTask;
use Illuminate\Http\Request;

class HomeworkCopilotController extends Controller
{
    public function index()
    {
        $tasks = FamilyTask::with('student')->get();

        return view('fase62.homework-copilot.dashboard', compact('tasks'));
    }

    public function showTaskDetails($id)
    {
        $task = FamilyTask::findOrFail($id);

        return view('fase62.homework-copilot.task-guide', compact('task'));
    }

    public function markAsReviewed(Request $request, $id)
    {
        $task = FamilyTask::findOrFail($id);
        $task->update(['parent_reviewed' => true]);

        return redirect()->back();
    }

    public function submitParentFeedback(Request $request, $id)
    {
        $task = FamilyTask::findOrFail($id);
        $task->update(['parent_feedback' => $request->feedback]);

        return redirect()->back();
    }

    public function teacherView()
    {
        $tasks = FamilyTask::with('student')->get();

        return view('fase62.homework-copilot.teacher-view', compact('tasks'));
    }
}
