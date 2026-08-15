<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
    public function store(Request $request) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}
    public function manager()
    {
        return view('fase61.achievements.manager');
    }
}
