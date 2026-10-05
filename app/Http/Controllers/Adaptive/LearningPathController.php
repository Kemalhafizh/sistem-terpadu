<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Support\Access\StudentDataAccess;

class LearningPathController extends Controller
{
    public function index()
    {
        return view('adaptive.learning-path.index');
    }

    public function show($pathId)
    {
        StudentDataAccess::authorizeStudentRecord(request()->user(), LearningPath::find($pathId)?->student);

        return view('adaptive.learning-path.show', compact('pathId'));
    }

    public function generate($studentId, $subjectId)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }
}
