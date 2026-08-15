<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'drip_campaign_id',
        'step_order',
        'delay_minutes',
        'omnichannel_template_id',
        'conditions',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(DripCampaign::class, 'drip_campaign_id');
    }

    public function template()
    {
        return $this->belongsTo(OmnichannelTemplate::class, 'omnichannel_template_id');
    }
}
