<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRead extends Model
{
    use HasFactory;

    protected $table = 'announcement_reads';

    protected $fillable = [
        'announcement_id',
        'user_id',
        'read_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Get the announcement related to this read record.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the user who read the announcement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
