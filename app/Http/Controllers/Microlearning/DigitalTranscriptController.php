<?php
namespace App\Http\Controllers\Microlearning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DigitalTranscriptController extends Controller
{
    public function index() { return view('transcripts.student-view'); }
    public function show($id) { return view('transcripts.student-view'); }
    public function verify($hash) { return view('transcripts.public-verification'); }
    public function badgeGallery() { return view('transcripts.student-view'); }
    public function adminManager() { return view('transcripts.admin-manager'); }
}
