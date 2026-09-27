<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrowdfundCampaignController extends Controller
{
    public function index()
    {
        return view('fase61.crowdfunding.campaign-list');
    }

    public function create() {}

    public function store(Request $request) {}

    public function show($id)
    {
        return view('fase61.crowdfunding.campaign-detail');
    }

    public function update(Request $request, $id) {}

    public function donate($id) {}

    public function donorReport()
    {
        return view('fase61.crowdfunding.donor-report');
    }
}
