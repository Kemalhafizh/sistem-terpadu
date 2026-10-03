<?php

namespace App\Http\Controllers\Adaptive;

use App\Http\Controllers\Controller;
use App\Services\Adaptive\IrtCalibrationService;

class IrtCalibrationController extends Controller
{
    public function dashboard()
    {
        return view('adaptive.calibration.dashboard');
    }

    public function batchCalibrate($examId, IrtCalibrationService $calibration)
    {
        $result = $calibration->batchCalibrateExam((int) $examId);

        if ($result['calibrated_count'] === 0) {
            return redirect()->back()->with('info', 'Belum ada jawaban siswa pada ujian ini, jadi belum ada soal yang bisa dikalibrasi.');
        }

        return redirect()->back()->with('success', "{$result['calibrated_count']} soal dikalibrasi dari jawaban siswa; {$result['skipped_count']} soal belum memiliki jawaban.");
    }

    public function itemDetail($questionId)
    {
        return view('adaptive.calibration.item-detail', compact('questionId'));
    }
}
