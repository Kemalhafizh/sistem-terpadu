<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataQualityRule extends Model
{
    use HasFactory;

    protected $fillable = ['target_table', 'target_column', 'rule_type', 'rule_definition', 'severity_level', 'is_active'];

    public function anomalies()
    {
        return $this->hasMany(AnomalyRecord::class);
    }
}
