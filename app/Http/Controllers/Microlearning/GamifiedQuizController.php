<?php

namespace App\Http\Controllers\Microlearning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GamifiedQuizController extends Controller
{
    public function index()
    {
        return view('gamified-quizzes.dashboard');
    }

    public function dashboard()
    {
        return view('gamified-quizzes.dashboard');
    }

    public function create()
    {
        return view('gamified-quizzes.dashboard');
    }

    public function store(Request $request)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function host($id)
    {
        return view('gamified-quizzes.lobby');
    }

    public function join()
    {
        return view('gamified-quizzes.dashboard');
    }

    public function liveView($id)
    {
        return view('gamified-quizzes.live');
    }

    public function results($id)
    {
        return view('gamified-quizzes.results');
    }
}
