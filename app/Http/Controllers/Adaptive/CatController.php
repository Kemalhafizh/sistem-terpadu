<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;
use App\Models\CatSession;
use Illuminate\Http\Request;

class CatController extends Controller
{
    public function startExam($examId)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function takeExam($sessionId)
    {
        $this->ownSession($sessionId);

        return view('adaptive.cat.take-exam', compact('sessionId'));
    }

    public function processResponse(Request $request, $sessionId)
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function result($sessionId)
    {
        $this->ownSession($sessionId);

        return view('adaptive.cat.result', compact('sessionId'));
    }

    /** The caller's own CAT session, else 404 (looked up by id and owner). */
    private function ownSession($sessionId): CatSession
    {
        return CatSession::whereKey($sessionId)
            ->whereHas('student', fn ($q) => $q->where('user_id', request()->user()->id))
            ->firstOrFail();
    }
}
