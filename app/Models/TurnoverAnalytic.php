<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoverAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'department', 'year', 'month', 'headcount_start', 'terminations', 'turnover_rate'
    ];
}