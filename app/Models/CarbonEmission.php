<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarbonEmission extends Model
{
    use HasFactory;

    protected $table = 'carbon_emissions';

    protected $fillable = [
        'period',
        'co2_kg',
        'source_category',
    ];
}
