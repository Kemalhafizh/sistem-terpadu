<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpsCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_title',
        'start_date',
        'end_date',
        'target_audience',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses()
    {
        return $this->hasMany(NpsResponse::class);
    }
}
