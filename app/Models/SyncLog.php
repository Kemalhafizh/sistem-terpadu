<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'sync_type',
        'records_synced',
        'conflicts_resolved',
        'status',
        'payload_summary',
    ];

    protected $casts = [
        'records_synced' => 'integer',
        'conflicts_resolved' => 'integer',
        'payload_summary' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
