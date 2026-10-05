<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SharedResourceController extends Controller
{
    public function index()
    {
        return view('knowledge.resources.index');
    }

    public function create()
    {
        return view('knowledge.resources.upload');
    }

    public function store(Request $request)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function show($id)
    {
        return view('knowledge.resources.show');
    }

    public function rate(Request $request, $id)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }
}
