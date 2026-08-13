<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_id',
        'measurement_date',
        'value',
        'evidence',
        'analysis',
        'recorded_by',
    ];
}
