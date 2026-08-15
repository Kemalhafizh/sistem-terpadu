<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpsResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'nps_campaign_id',
        'parent_id',
        'score',
        'verbatim_feedback',
        'category',
    ];

    public function campaign()
    {
        return $this->belongsTo(NpsCampaign::class, 'nps_campaign_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
