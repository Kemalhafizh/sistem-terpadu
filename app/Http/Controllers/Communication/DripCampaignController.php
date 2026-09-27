<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\DripCampaign;
use Illuminate\Http\Request;

class DripCampaignController extends Controller
{
    public function index()
    {
        $campaigns = DripCampaign::all();

        return view('drip-campaigns.analytics', compact('campaigns'));
    }

    public function create()
    {
        return view('drip-campaigns.create');
    }

    public function store(Request $request)
    {
        DripCampaign::create($request->all());

        return redirect()->route('drip_campaigns.index');
    }

    public function builder($id)
    {
        $campaign = DripCampaign::with('steps.template')->findOrFail($id);

        return view('drip-campaigns.builder', compact('campaign'));
    }

    public function toggleStatus($id)
    {
        $campaign = DripCampaign::findOrFail($id);
        $campaign->update(['is_active' => ! $campaign->is_active]);

        return back();
    }
}
