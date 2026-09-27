<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\NpsCampaign;
use Illuminate\Http\Request;

class NpsController extends Controller
{
    public function dashboard()
    {
        $campaigns = NpsCampaign::all();

        return view('nps.dashboard', compact('campaigns'));
    }

    public function campaigns()
    {
        $campaigns = NpsCampaign::all();

        return view('nps.campaign-manager', compact('campaigns'));
    }

    public function createCampaign()
    {
        return view('nps.create_campaign');
    }

    public function storeCampaign(Request $request)
    {
        NpsCampaign::create($request->all());

        return redirect()->route('nps.campaigns');
    }

    public function responses($campaignId)
    {
        $campaign = NpsCampaign::with('responses.parent')->findOrFail($campaignId);

        return view('nps.responses', compact('campaign'));
    }

    public function detractorFollowup()
    {
        // Returns detractors for follow up
        return view('nps.detractors');
    }
}
