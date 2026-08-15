<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DripCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_name',
        'trigger_event',
        'is_active',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps()
    {
        return $this->hasMany(CampaignStep::class);
    }
}
