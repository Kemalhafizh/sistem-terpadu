<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasteryController extends Controller
{
    public function heatmap($classroomId)
    {
        return view('adaptive.mastery.heatmap', compact('classroomId'));
    }

    public function studentProfile($studentId)
    {
        return view('adaptive.mastery.student-profile', compact('studentId'));
    }

    public function classOverview($classroomId)
    {
        return view('adaptive.mastery.class-overview', compact('classroomId'));
    }
}
