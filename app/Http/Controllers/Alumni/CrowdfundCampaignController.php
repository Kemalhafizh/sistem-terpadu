<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;

class CrowdfundCampaignController extends Controller
{
    public function index()
    {
        return view('fase61.crowdfunding.campaign-list');
    }

    public function show($id)
    {
        return view('fase61.crowdfunding.campaign-detail');
    }

    public function donorReport()
    {
        return view('fase61.crowdfunding.donor-report');
    }
}
