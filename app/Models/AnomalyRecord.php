<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnomalyRecord extends Model
{
    use HasFactory;

    protected $fillable = ['data_quality_rule_id', 'record_id', 'invalid_data', 'status', 'resolved_by'];

    protected $casts = ['invalid_data' => 'array'];

    public function rule()
    {
        return $this->belongsTo(DataQualityRule::class, 'data_quality_rule_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
