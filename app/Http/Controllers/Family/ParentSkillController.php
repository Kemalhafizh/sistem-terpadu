<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\ParentSkill;
use Illuminate\Http\Request;

class ParentSkillController extends Controller
{
    public function directory()
    {
        $skills = ParentSkill::with('student')->get();

        return view('fase62.skill-sharing.directory', compact('skills'));
    }

    public function registration()
    {
        return view('fase62.skill-sharing.registration');
    }

    public function searchDirectory(Request $request)
    {
        return redirect()->route('parent_skills.directory');
    }

    public function sessionScheduler()
    {
        return view('fase62.skill-sharing.session-scheduler');
    }
}
