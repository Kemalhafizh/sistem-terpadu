<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;

class LearningPathController extends Controller
{
    public function index()
    {
        return view('adaptive.learning-path.index');
    }

    public function show($pathId)
    {
        return view('adaptive.learning-path.show', compact('pathId'));
    }

    public function generate($studentId, $subjectId)
    {
        // logic
        return redirect()->back();
    }
}
