<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UatFeedback extends Model
{
    use HasFactory;

    protected $table = 'uat_feedback';

    protected $fillable = [
        'user_id',
        'page_url',
        'rating',
        'category',
        'description',
        'screenshot_path',
        'device_info',
        'status',
        'priority',
        'developer_notes',
    ];

    protected $casts = [
        'device_info' => 'array',
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
