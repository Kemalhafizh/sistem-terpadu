<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Support\Access\StudentDataAccess;

class MasteryController extends Controller
{
    public function heatmap($classroomId)
    {
        abort_unless(Classroom::find($classroomId)?->isVisibleTo(request()->user()), 404);

        return view('adaptive.mastery.heatmap', compact('classroomId'));
    }

    public function studentProfile($studentId)
    {
        StudentDataAccess::authorizeStudentRecord(request()->user(), Student::find($studentId));

        return view('adaptive.mastery.student-profile', compact('studentId'));
    }

    public function classOverview($classroomId)
    {
        abort_unless(Classroom::find($classroomId)?->isVisibleTo(request()->user()), 404);

        return view('adaptive.mastery.class-overview', compact('classroomId'));
    }
}
