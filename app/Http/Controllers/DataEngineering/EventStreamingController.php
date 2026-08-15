<?php
namespace App\Http\Controllers\DataEngineering;
use App\Http\Controllers\Controller;
use App\Services\DataEngineering\EventStreamingService;
class EventStreamingController extends Controller {
    protected $service;
    public function __construct(EventStreamingService $service) { $this->service = $service; }
    public function monitorDashboard() { return view('data-engineering.streams'); }
    public function streamLogs() { return view('data-engineering.replay'); }
}
