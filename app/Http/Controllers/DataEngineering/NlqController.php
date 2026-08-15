<?php
namespace App\Http\Controllers\DataEngineering;
use App\Http\Controllers\Controller;
use App\Services\DataEngineering\NlqService;
use Illuminate\Http\Request;
class NlqController extends Controller {
    protected $service;
    public function __construct(NlqService $service) { $this->service = $service; }
    public function searchInterface() { return view('nlq.search'); }
    public function executeQuery(Request $request) { return redirect()->back(); }
    public function dictionaryMapping() { return view('nlq.dictionary'); }
}
