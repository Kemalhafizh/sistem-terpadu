<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IrtCalibrationController extends Controller
{
    public function dashboard()
    {
        return view('adaptive.calibration.dashboard');
    }

    public function batchCalibrate($examId)
    {
        // logic
        return redirect()->back();
    }

    public function itemDetail($questionId)
    {
        return view('adaptive.calibration.item-detail', compact('questionId'));
    }
}
