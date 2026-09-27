<?php

namespace App\Services\Communication;

use App\Models\NpsCampaign;
use App\Models\NpsResponse;

class NpsService
{
    public function launchCampaign($data)
    {
        return NpsCampaign::create($data);
    }

    public function recordResponse($data)
    {
        $category = 'passive';
        if ($data['score'] >= 9) {
            $category = 'promoter';
        } elseif ($data['score'] <= 6) {
            $category = 'detractor';
        }

        $data['category'] = $category;

        return NpsResponse::create($data);
    }

    public function calculateNpsScore(NpsCampaign $campaign)
    {
        $total = $campaign->responses()->count();
        if ($total === 0) {
            return 0;
        }

        $promoters = $campaign->responses()->where('category', 'promoter')->count();
        $detractors = $campaign->responses()->where('category', 'detractor')->count();

        return (($promoters / $total) * 100) - (($detractors / $total) * 100);
    }
}
