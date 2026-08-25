<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileSyncLog extends Model
{
    use HasFactory;

    protected $table = 'mobile_sync_logs';

    protected $fillable = [
        'user_id',
        'device_id',
        'sync_type',
        'records_synced',
        'synced_at',
    ];

    protected $casts = [
        'records_synced' => 'integer',
        'synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
