<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcoProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'project_title',
        'description',
        'sdg_alignment',
        'start_date',
        'end_date',
        'status',
        'participating_classes',
        'impact_result'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sdg_alignment' => 'array',
        'participating_classes' => 'array',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
