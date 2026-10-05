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
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
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
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function destroy($id)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function publish($id)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function analytics()
    {
        return view('micro-lessons.analytics');
    }
}
