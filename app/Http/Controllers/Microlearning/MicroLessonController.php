<?php

namespace App\Http\Controllers\Microlearning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MicroLessonController extends Controller
{
    public function index()
    {
        return view('micro-lessons.index');
    }

    public function create()
    {
        return view('micro-lessons.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('micro-lessons.index');
    }

    public function show($id)
    {
        return view('micro-lessons.show');
    }

    public function edit($id)
    {
        return view('micro-lessons.create');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('micro-lessons.index');
    }

    public function destroy($id)
    {
        return redirect()->route('micro-lessons.index');
    }

    public function publish($id)
    {
        return redirect()->route('micro-lessons.index');
    }

    public function analytics()
    {
        return view('micro-lessons.analytics');
    }
}
