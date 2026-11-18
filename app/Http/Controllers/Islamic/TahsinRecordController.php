<?php

namespace App\Http\Controllers\Islamic;

use App\Http\Controllers\Controller;

class TahsinRecordController extends Controller
{
    public function index()
    {
        return view('tahsin.index');
    }

    public function show($id)
    {
        return view('tahsin.show');
    }

    public function edit($id)
    {
        return view('tahsin.form');
    }

    public function analytics()
    {
        return view('tahsin.analytics');
    }
}
