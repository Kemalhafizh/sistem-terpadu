<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhiteboardSession extends Model
{
    use HasFactory;

    protected $table = 'whiteboard_sessions';

    protected $fillable = [
        'session_id',
        'title',
        'host_id',
        'status',
        'canvas_state_json',
        'snapshot_image_path',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}
