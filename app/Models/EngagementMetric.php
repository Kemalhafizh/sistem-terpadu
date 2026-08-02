<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementMetric extends Model
{
    use HasFactory;

    protected $table = 'engagement_metrics';

    protected $fillable = [
        'parent_user_id',
        'period_start',
        'period_end',
        'login_count',
        'message_replies',
        'event_attendance',
        'engagement_score',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }
}
