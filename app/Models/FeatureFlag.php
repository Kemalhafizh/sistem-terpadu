<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
        'rollout_percentage',
        'target_roles',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rollout_percentage' => 'integer',
        'target_roles' => 'array',
    ];
}
