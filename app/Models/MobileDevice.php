<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileDevice extends Model
{
    use HasFactory;

    protected $table = 'mobile_devices';

    protected $fillable = [
        'parent_user_id',
        'device_token',
        'platform',
        'app_version',
        'last_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }
}
