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
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function show($id)
    {
        return view('knowledge.lesson-study.show');
    }

    public function submitObservation(Request $request, $id)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function advancePhase($id)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }
}
