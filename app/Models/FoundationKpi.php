<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundationKpi extends Model
{
    use HasFactory;

    protected $fillable = ['foundation_unit_id', 'kpi_name', 'category', 'target_value', 'actual_value', 'unit', 'fiscal_year'];

    protected $casts = ['target_value' => 'decimal:2', 'actual_value' => 'decimal:2'];
}
