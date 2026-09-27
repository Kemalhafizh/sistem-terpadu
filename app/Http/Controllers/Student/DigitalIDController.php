<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DigitalIdCard;

class DigitalIDController extends Controller
{
    public function showMyCard()
    {
        $card = DigitalIdCard::with('student')->where('student_id', auth()->id())->first();

        return view('student.idcard.virtual-card', compact('card'));
    }

    public function scanner()
    {
        return view('student.idcard.scanner');
    }
}
