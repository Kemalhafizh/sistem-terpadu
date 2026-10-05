<?php

namespace App\Http\Controllers\Islamic;

use App\Http\Controllers\Controller;

class IslamicEventController extends Controller
{
    public function index()
    {
        return view('fase57.islamic-events.calendar');
    }

    public function calendar()
    {
        return view('fase57.islamic-events.calendar');
    }

    public function show($id)
    {
        return view('fase57.islamic-events.show');
    }

    public function prayerTimes()
    {
        return view('fase57.islamic-events.prayer-times');
    }
}
