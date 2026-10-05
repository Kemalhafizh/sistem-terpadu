<?php

namespace App\Http\Controllers\Islamic;

use App\Http\Controllers\Controller;

class TahsinRecordController extends Controller
{
    public function index()
    {
        return view('fase57.tahsin.index');
    }

    public function show($id)
    {
        return view('fase57.tahsin.show');
    }

    public function edit($id)
    {
        return view('fase57.tahsin.form');
    }

    public function analytics()
    {
        return view('fase57.tahsin.analytics');
    }
}
