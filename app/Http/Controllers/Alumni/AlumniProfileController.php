<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;

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

    public function reunionPlanner()
    {
        return view('fase61.alumni-directory.reunion-planner');
    }
}
