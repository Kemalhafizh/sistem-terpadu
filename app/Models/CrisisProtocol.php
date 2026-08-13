<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrisisProtocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'crisis_type', 'steps', 'emergency_contacts', 'assembly_points',
        'special_instructions', 'is_active', 'approved_by'
    ];

    protected $casts = [
        'steps' => 'array',
        'emergency_contacts' => 'array',
        'assembly_points' => 'array',
        'is_active' => 'boolean',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}