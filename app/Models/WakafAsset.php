<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WakafAsset extends Model
{
    use HasFactory;

    protected $fillable = ['asset_code', 'asset_name', 'asset_type', 'description', 'location', 'acquisition_value', 'current_value', 'acquisition_date', 'wakif_name', 'utilization_status', 'utilization_description', 'annual_income', 'certificate_number'];

    protected $casts = ['acquisition_date' => 'date', 'acquisition_value' => 'decimal:2', 'current_value' => 'decimal:2', 'annual_income' => 'decimal:2'];
}
