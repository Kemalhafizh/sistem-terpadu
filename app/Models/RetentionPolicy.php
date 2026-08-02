<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RetentionPolicy extends Model
{
    use HasFactory;

    protected $table = 'retention_policies';

    protected $fillable = [
        'data_category',
        'retention_months',
        'action',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function purgeLogs(): HasMany
    {
        return $this->hasMany(PurgeLog::class, 'policy_id');
    }
}
