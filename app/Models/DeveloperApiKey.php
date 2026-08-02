<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperApiKey extends Model
{
    use HasFactory;

    protected $table = 'developer_api_keys';

    protected $fillable = [
        'user_id',
        'app_name',
        'api_key',
        'scopes',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
