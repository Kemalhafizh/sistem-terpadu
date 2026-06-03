<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'name',
        'credentials',
        'settings',
        'is_active',
        'last_sync_at',
    ];

    protected $casts = [
        'credentials' => 'encrypted',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(IntegrationLog::class, 'config_id');
    }
}
