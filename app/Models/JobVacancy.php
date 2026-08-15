<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_title', 'department', 'requirements', 'status', 'closing_date', 'created_by'
    ];

    protected $casts = [
        'closing_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applicants()
    {
        return $this->hasMany(JobApplicant::class);
    }
}