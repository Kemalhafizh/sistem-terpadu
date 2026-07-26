<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CharacterDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'icon_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assessments(): HasMany
    {
        return $this->hasMany(CharacterAssessment::class);
    }
}
