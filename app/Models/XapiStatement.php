<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XapiStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'verb',
        'object_type',
        'object_id',
        'result',
        'context',
        'timestamp_at',
        'stored_at',
    ];

    protected $casts = [
        'result' => 'array',
        'context' => 'array',
        'timestamp_at' => 'datetime',
        'stored_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
