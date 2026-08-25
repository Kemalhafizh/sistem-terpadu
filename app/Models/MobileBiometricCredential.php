<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileBiometricCredential extends Model
{
    use HasFactory;

    protected $table = 'mobile_biometric_credentials';

    protected $fillable = [
        'user_id',
        'device_id',
        'public_key',
        'biometric_type',
        'is_enabled',
        'last_used_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
