<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WellnessProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_name', 'category', 'description', 'points_reward', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(WellnessLog::class);
    }
}