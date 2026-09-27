<?php

namespace App\Http\Controllers\Islamic;

use App\Http\Controllers\Controller;

class TahsinRecordController extends Controller
{
    public function index()
    {
        return view('fase57.tahsin.index');
    }

    public function create()
    {
        return view('fase57.tahsin.form');
    }

    public function store()
    {
        return redirect()->route('tahsin.index');
    }

    public function show($id)
    {
        return view('fase57.tahsin.show');
    }

    public function edit($id)
    {
        return view('fase57.tahsin.form');
    }

    public function update($id)
    {
        return redirect()->route('tahsin.index');
    }

    public function destroy($id)
    {
        return redirect()->route('tahsin.index');
    }

    public function analytics()
    {
        return view('fase57.tahsin.analytics');
    }
}
