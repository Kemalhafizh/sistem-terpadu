<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentShowcase extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'project_title', 'description', 'cover_image_url', 'media_urls', 'is_public', 'view_count'
    ];

    protected $casts = [
        'media_urls' => 'array',
        'is_public' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function endorsements()
    {
        return $this->hasMany(ShowcaseEndorsement::class, 'showcase_id');
    }
}
