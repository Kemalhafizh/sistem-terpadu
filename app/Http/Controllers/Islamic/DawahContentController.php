<?php

namespace App\Http\Controllers\Islamic;

use App\Http\Controllers\Controller;

class DawahContentController extends Controller
{
    public function index()
    {
        return view('fase57.dawah.hub');
    }

    public function hub()
    {
        return view('fase57.dawah.hub');
    }

    public function show($id)
    {
        return view('fase57.dawah.hub');
    }

    public function edit($id)
    {
        return view('fase57.dawah.editor');
    }
}
