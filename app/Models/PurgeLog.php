<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurgeLog extends Model
{
    use HasFactory;

    protected $table = 'purge_logs';

    public $timestamps = false;

    protected $fillable = [
        'policy_id',
        'records_affected',
        'details',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(RetentionPolicy::class, 'policy_id');
    }
}
