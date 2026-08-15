<?php
namespace App\Http\Controllers\Microlearning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GamifiedQuizController extends Controller
{
    public function index() { return view('gamified-quizzes.dashboard'); }
    public function dashboard() { return view('gamified-quizzes.dashboard'); }
    public function create() { return view('gamified-quizzes.dashboard'); }
    public function store(Request $request) { return redirect()->route('gamified-quizzes.dashboard'); }
    public function host($id) { return view('gamified-quizzes.lobby'); }
    public function join() { return view('gamified-quizzes.dashboard'); }
    public function liveView($id) { return view('gamified-quizzes.live'); }
    public function results($id) { return view('gamified-quizzes.results'); }
}
