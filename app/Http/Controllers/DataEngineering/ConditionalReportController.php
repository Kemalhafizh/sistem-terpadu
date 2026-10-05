<?php

namespace App\Http\Controllers\DataEngineering;

use App\Http\Controllers\Controller;
use App\Services\DataEngineering\ConditionalReportService;

class ConditionalReportController extends Controller
{
    protected $service;

    public function __construct(ConditionalReportService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('report-scheduler.manager');
    }

    public function create()
    {
        return view('report-scheduler.create');
    }

    public function store()
    {
        // FASE 118.3: no backend yet; this used to pretend the change was saved.
        abort(501, __('Fitur ini belum tersedia. Perubahan belum bisa disimpan.'));
    }

    public function logs()
    {
        return view('report-scheduler.execution-logs');
    }

    public function manualTrigger($id)
    {
        return redirect()->back();
    }
}
