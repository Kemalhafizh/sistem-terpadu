<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumni_profile_id',
        'achievement_title',
        'category',
        'date_achieved',
        'description',
        'media_link',
        'is_featured_in_hall_of_fame',
    ];

    protected $casts = [
        'date_achieved' => 'date',
        'is_featured_in_hall_of_fame' => 'boolean',
    ];

    public function alumniProfile()
    {
        return $this->belongsTo(AlumniProfile::class);
    }
}
