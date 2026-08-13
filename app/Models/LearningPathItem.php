<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPathItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_path_id',
        'item_type',
        'title',
        'content_reference',
        'estimated_minutes',
        'sort_order',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'sort_order' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
    }
}
