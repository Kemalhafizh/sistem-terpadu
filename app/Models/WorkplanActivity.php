<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkplanActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'activity_name',
        'output_target',
        'start_date',
        'end_date',
        'budget',
        'realization',
        'status',
        'evidence_notes',
    ];
}
