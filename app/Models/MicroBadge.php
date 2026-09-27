<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroBadge extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image_url', 'awarding_criteria'];

    protected $casts = [
        'awarding_criteria' => 'array',
    ];

    public function studentBadges()
    {
        return $this->hasMany(StudentBadge::class);
    }
}
