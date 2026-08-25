<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrictionlessAttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gate_camera_id',
        'gate_location',
        'confidence_score',
        'liveness_verified',
        'capture_timestamp',
        'status',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'liveness_verified' => 'boolean',
        'capture_timestamp' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
