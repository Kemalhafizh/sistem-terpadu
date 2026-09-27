<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LessonStudyController extends Controller
{
    public function index()
    {
        return view('knowledge.lesson-study.index');
    }

    public function create()
    {
        return view('knowledge.lesson-study.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('knowledge.lesson-study.index');
    }

    public function show($id)
    {
        return view('knowledge.lesson-study.show');
    }

    public function submitObservation(Request $request, $id)
    {
        return back();
    }

    public function advancePhase($id)
    {
        return back();
    }
}
