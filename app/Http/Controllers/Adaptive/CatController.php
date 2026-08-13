<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CatController extends Controller
{
    public function startExam($examId)
    {
        // logic
        return redirect()->route('adaptive.cat.take', ['sessionId' => 1]);
    }

    public function takeExam($sessionId)
    {
        return view('adaptive.cat.take-exam', compact('sessionId'));
    }

    public function processResponse(Request $request, $sessionId)
    {
        // logic
        return redirect()->back();
    }

    public function result($sessionId)
    {
        return view('adaptive.cat.result', compact('sessionId'));
    }
}
