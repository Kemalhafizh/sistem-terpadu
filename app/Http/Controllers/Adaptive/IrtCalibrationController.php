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
            return redirect()->back()->with('info', __('Belum ada jawaban siswa pada ujian ini, jadi belum ada soal yang bisa dikalibrasi.'));
        }

        return redirect()->back()->with('success', __(':calibrated soal dikalibrasi dari jawaban siswa; :skipped soal belum memiliki jawaban.', [
            'calibrated' => $result['calibrated_count'], 'skipped' => $result['skipped_count'],
        ]));
    }

    public function itemDetail($questionId)
    {
        return view('adaptive.calibration.item-detail', compact('questionId'));
    }
}
