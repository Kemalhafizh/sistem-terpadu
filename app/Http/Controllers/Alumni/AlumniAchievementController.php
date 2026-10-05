<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;

class AlumniAchievementController extends Controller
{
    public function hallOfFame()
    {
        return view('fase61.achievements.hall-of-fame');
    }

    public function create()
    {
        return view('fase61.achievements.submission');
    }

    public function manager()
    {
        return view('fase61.achievements.manager');
    }
}
