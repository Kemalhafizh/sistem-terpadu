<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlumniProfileController extends Controller
{
    public function index()
    {
        return view('fase61.alumni-directory.network');
    }

    public function show($id)
    {
        return view('fase61.alumni-directory.profile');
    }

    public function edit($id) {}

    public function update(Request $request, $id) {}

    public function directory() {}

    public function reunionPlanner()
    {
        return view('fase61.alumni-directory.reunion-planner');
    }
}
