<?php
namespace App\Http\Controllers\Treasury;
use App\Http\Controllers\Controller;
use App\Models\DailyCashPosition;

class TreasuryPositionController extends Controller {
    public function dashboard() {
        $positions = DailyCashPosition::with('account')->get();
        return view('treasury.position.dashboard', compact('positions'));
    }
    public function trend() {
        return view('treasury.position.trend');
    }
}